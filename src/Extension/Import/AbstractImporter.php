<?php

namespace ASMBS\ScheduleBuilder\Extension\Import;

use Port\Csv\CsvReader;
use Port\Exception\WriterException;
use Port\Reader;
use Port\Result;
use Port\Steps\StepAggregator as Workflow;
use Port\Writer\CallbackWriter;

/**
 * @author  Kyle Tucker <kyleatucker@gmail.com>
 */
abstract class AbstractImporter implements ImporterInterface
{
    const FILE_INPUT = 'import_csv';

    /** @var  bool */
    protected $replace = false;

    /** @var  array */
    protected $notices = [];

    /** @var  Result */
    protected $result;

    /**
     * {@inheritdoc}
     */
    public static function load()
    {
        return new static();
    }

    /**
     * Constructor; registers hooks to set up the importer.
     */
    protected function __construct()
    {
        add_action('admin_menu', [$this, 'register'], 100);
        add_action('admin_init', [$this, 'handleSubmission']);

        add_action('admin_notices', [$this, 'printNotices']);
    }

    /**
     * Determine whether the import page is active.
     *
     * @return  bool
     */
    protected function isActive()
    {
        return (
            is_admin() &&
            isset($_REQUEST['page']) &&
            $_REQUEST['page'] === static::SLUG
        );
    }

    /**
     * Return a callback writer that dumps the contents of the row into an admin notice block.
     *
     * @return  CallbackWriter
     */
    protected function getDebugWriter()
    {
        $self = $this;
        return new CallbackWriter(function ($row) use ($self) {
            $self->addNotice(sprintf('<pre>%s</pre>', print_r($row, true)), 'info');
        });
    }

    /**
     * Process the workflow's result object.
     */
    protected function processResult()
    {
        $succeeded = $this->result->getSuccessCount();
        $failed = $this->result->getTotalProcessedCount() - $succeeded;

        if ($succeeded > 0) {
            $this->addNotice(sprintf('Successfully imported %s records.', $succeeded), 'success');
        }
        if ($failed > 0) {
            $this->addNotice(sprintf('%s records contained errors and were skipped.', $failed), 'error');

            // Format exception messages for display
            $messages = [];
            $i = 0;

            /**
             * @var int $row
             * @var WriterException $exception
             */
            foreach ($this->result->getExceptions() as $row => $exception) {
                $messages[] = sprintf('<b>Row #%s:</b> %s', $row, $exception->getMessage());
                $i++;

                if ($i === 10) {
                    // If there are more than 10 errors recorded, stop here and add a line indicating
                    // that there are more errors that haven't been shown
                    $remainingErrors = $this->result->getErrorCount() - $i;
                    $messages[] = sprintf('<i>...and %s more errors</i>', $remainingErrors);

                    break;
                }
            }

            // Add an additional notice to display up to 10 of the logged import errors
            $this->addNotice(sprintf(
                '<p><b>Errors:</b></p><ul><li>%s</li></ul>',
                implode('</li><li>', $messages)
            ), 'warning');
        }
    }

    /**
     * Enqueue an admin notice.
     *
     * @param   string $message
     * @param   string $context
     * @return  $this
     */
    public function addNotice($message, $context = 'info')
    {
        $this->notices[$context][] = $message;

        return $this;
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        add_submenu_page(
            'edit.php?post_type=' . $this->getPostType(),
            $this->getPageTitle(),
            $this->getMenuTitle(),
            'manage_options',
            static::SLUG,
            [$this, 'renderPage']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function renderPage()
    {
        ?>
        <div class="wrap">
            <h2><?php echo $this->getPageTitle() ?></h2>
            <p>Select a CSV file containing the data you want to import.<br>
                <strong>NOTE:</strong> This importer enforces the following requirements:</p>
            <ol>
                <li>Fields MUST be <b>separated by commas</b></li>
                <li>Fields MAY be <b>enclosed in double quotes (<code>&quot;</code>)</b>.</li>
                <li>Rows MUST be terminated by a <b>newline</b>.</li>
                <li>The file MUST contain <b>exactly one (1) header row.</b></li>
                <li>The file MUST contain the following columns, in order (headers do not need to match):<br>
                    <?php echo implode(' | ', array_map(function ($column) {
                        return sprintf('<code>%s</code>', strtoupper($column));
                    }, $this->getColumns())) ?></li>
            </ol>
            <hr>
            <form method="post" enctype="multipart/form-data">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="import_file">File to Import</label></th>
                        <td>
                            <input type="file" name="<?php echo static::FILE_INPUT ?>" id="import_file"
                                   accept="text/csv,text/plain">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="import_replace_true">Existing Records</label></th>
                        <td>
                            <p>
                                <label>
                                    <input type="radio" name="replace" id="import_replace_true" value="1">
                                    <span>Replace</span>
                                </label>
                                <br>
                                <label>
                                    <input type="radio" name="replace" id="import_replace_false" value="0"
                                           checked="checked">
                                    <span>Skip</span>
                                </label>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Upload & Process Data') ?>
            </form>
        </div>
        <?php
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Handle the import form's submission.
     * @throws \Port\Exception
     */
    public function handleSubmission()
    {
        if (!$this->isActive() || !isset($_FILES[static::FILE_INPUT])) {
            return;
        }

        $file = $this->handleUpload($_FILES[static::FILE_INPUT]);

        if ($file) {
            $this->replace = isset($_POST['replace']) && $_POST['replace'] == 1 ? true : false;
            $this->processFile($file);
            $this->processResult();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleUpload(array $file)
    {
        $error = null;
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $error = 'The file you selected exceeds the maximum upload size.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error = 'The file upload was interrupted.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $error = 'You must select a file to upload.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                case UPLOAD_ERR_CANT_WRITE:
                    $error = 'Unable to write the uploaded file to the server.';
                    break;
                default:
                    $error = 'An unknown error occurred.';
            }
        }

        if ($error) {
            $this->addNotice($error, 'error');

            return null;
        }

        return new \SplFileInfo($file['tmp_name']);
    }

    /**
     * {@inheritdoc}
     * @throws \Port\Exception
     */
    public function processFile(\SplFileInfo $file, $replace = false)
    {
        $reader = new CsvReader($file->openFile());
        $reader->setStrict(false);
        $reader->setHeaderRowNumber(0);
        $reader->setColumnHeaders($this->getColumns());

        $workflow = $this->buildWorkflow($reader)
            ->setSkipItemOnFailure(true);

        $this->result = $workflow->process();
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Print any queued admin notices.
     */
    public function printNotices()
    {
        foreach ($this->notices as $context => $messages) {
            foreach ($messages as $message) {
                printf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $context, $message);
            }
        }
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Build a workflow object for processing the import.
     *
     * @param   Reader $reader
     * @return  Workflow
     */
    abstract protected function buildWorkflow(Reader $reader);
}
