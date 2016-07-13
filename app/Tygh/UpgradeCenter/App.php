<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Tygh\UpgradeCenter;

use Tygh\Exceptions\DatabaseException;
use Tygh\Exceptions\PHPErrorException;
use Tygh\UpgradeCenter\Migrations\Migration;
use Tygh\Http;
use Tygh\Registry;
use Tygh\Addons\SchemesManager;
use Tygh\Languages\Languages;
use Tygh\UpgradeCenter\Log;
use Tygh\UpgradeCenter\Output;
use Tygh\Themes\Themes;
use Tygh\DataKeeper;
use Tygh\Mailer;
use Tygh\Settings;

class App
{
    const PACKAGE_INSTALL_RESULT_SUCCESS = true;
    const PACKAGE_INSTALL_RESULT_FAIL = false;
    const PACKAGE_INSTALL_RESULT_WITH_ERRORS = null;

    /**
     * Instance of App
     *
     * @var App $instance
     */
    private static $instance;

    /**
     * Available upgrade connectors
     *
     * @var array $_connectors List of connectors
     */
    protected $connectors = array();

    /**
     * Global App config
     *
     * @var array $config
     */
    protected $config = array();

    /**
     * Init params
     *
     * @var array $params
     */
    protected $params = array();

    /**
     * Console mode flag
     *
     * @var bool $is_console
     */
    private $is_console = null;

    /**
     * Upgrade center settings
     *
     * @var array
     */
    protected $settings = array();

    /**
     * Perform backup before package installation flag
     *
     * @var bool
     */
    public $perform_backup = true;

    /**
     * Gets list of installed packages
     *
     * @param  int   $page           Active page
     * @param  int   $items_per_page Items per page
     * @return array List of packages
     */

    /**
     * Gets list of installed packages
     * @param array $params Select conditions
     *      int page Active page
     *      int items_per_page Elements count per page
     *      int id Package ID
     * @return array List of packages
     */
    public function getInstalledPackagesList($params = array())
    {
        $default_params = array(
            'page' => 1,
            'items_per_page' => 0,
        );

        $params = array_merge($default_params, $params);

        $condition = '';

        if (!empty($params['id'])) {
            $condition .= db_quote(' AND id = ?i', $params['id']);
        }

        if (!empty($params['items_per_page'])) {
            $total_items = db_get_field("SELECT COUNT(*) FROM ?:installed_upgrades WHERE 1 $condition");
            $limit = db_paginate($params['page'], $params['items_per_page'], $total_items);
        } else {
            $limit = '';
        }

        $packages = db_get_hash_array('SELECT * FROM ?:installed_upgrades WHERE 1 ?p ORDER BY `timestamp` DESC ' . $limit, 'id', $condition);

        return $packages;
    }

    /**
     * Gets list of available upgrade packages
     *
     * @return array List of packages
     */
    public function getPackagesList()
    {
        $packages = array();

        $pack_path = $this->getPackagesDir();
        $packages_dirs = fn_get_dir_contents($pack_path);

        if (!empty($packages_dirs)) {
            foreach ($packages_dirs as $package_id) {
                $schema = $this->getSchema($package_id);
                $schema['id'] = $package_id;

                if (!$this->validateSchema($schema)) {
                    continue;
                }

                if (is_file($pack_path . $package_id . '/' . $schema['file'])) {
                    $schema['ready_to_install'] = true;
                } else {
                    $schema['ready_to_install'] = false;
                }

                $packages[$schema['type']][$package_id] = $schema;
            }
        }

        return $packages;
    }

    /**
     * Sets notification to customer
     *
     * @param  string $type    Notification type (E - error, W - warning, N - notice)
     * @param  string $title   Notification title
     * @param  string $message Text of the notification
     * @return bool   true if notification was added to stack or displayed
     */
    public function setNotification($type, $title, $message)
    {
        if ($this->isConsole()) {
            echo "($type) $title: $message" . PHP_EOL;
            $result = true;
        } else {
            $result = fn_set_notification($type, $title, $message);
        }

        return $result;
    }

    /**
     * Checks and download upgrade schemas if available. Shows notification about new upgrades.
     * Uses data from the Upgrade Connectors.
     *
     * @param bool $show_upgrade_notice Flag that determines whether or not the message about new upgrades
     */
    public function checkUpgrades($show_upgrade_notice = true)
    {
        $connectors = $this->getConnectors();

        if (!empty($connectors)) {
            foreach ($connectors as $_id => $connector) {
                $data = $connector->getConnectionData();

                $headers = empty($data['headers']) ? array() : $data['headers'];
                if ($data['method'] == 'post') {
                    Http::mpost($data['url'], $data['data'], array(
                        'callback' => array(array(), $_id, $show_upgrade_notice),
                        'headers' => $headers));
                } else {
                    Http::mget($data['url'], $data['data'], array(
                        'callback' => array(array($this, 'processResponses'), $_id, $show_upgrade_notice),
                        'headers' => $headers));
                }
            }

            Http::processMultiRequest();
        }
    }

    /**
     * Resolves file conflicts
     *
     * @param  int    $package_id Package ID
     * @param  int    $file_id    File ID
     * @param  string $status     Resolve status (C - conflicts | R - resolved)
     * @return bool   true if updated, false - otherwise
     */
    public function resolveConflict($package_id, $file_id, $status)
    {
        $params = array('id' => $package_id);

        $packages = $this->getInstalledPackagesList($params);

        if (!isset($packages[$package_id]) || empty($packages[$package_id]['conflicts'])) {
            return false;
        }

        $conflicts = unserialize($packages[$package_id]['conflicts']);

        if (!isset($conflicts[$file_id])) {
            return false;
        } else {
            $conflicts[$file_id]['status'] = $status;
            $packages[$package_id]['conflicts'] = serialize($conflicts);

            db_query('UPDATE ?:installed_upgrades SET ?u WHERE id = ?i', $packages[$package_id], $package_id);
        }

        return true;
    }

    /**
     * Deletes all downloaded packages
     *
     * @return bool true if deleted
     */
    public function clearDownloadedPackages()
    {
        fn_rm($this->getPackagesDir());
        $created = fn_mkdir($this->getPackagesDir());

        return $created;
    }

    /**
     * Processes Upgrade Connectors responses.
     *
     * @param  string $response            Response text from specified upgrade server
     * @param  int    $connector_id        Connector ID from the connectors list
     * @param  bool   $show_upgrade_notice Flag that determines whether or not the message about new upgrades
     * @return mixed  Processing result from the Connector
     */
    public function processResponses($response, $connector_id, $show_upgrade_notice)
    {
        $schema = $this->connectors[$connector_id]->processServerResponse($response, $show_upgrade_notice);

        if (!empty($schema)) {
            $schema['id'] = $connector_id;
            $schema['type'] = $connector_id == 'core' ? 'core' : 'addon';

            if (!$this->validateSchema($schema)) {
                $this->setNotification('E', __('error'), __('uc_broken_upgrade_connector', array('[connector_id]' => $connector_id)));

                return false;
            }

            $pack_path = $this->getPackagesDir() . $connector_id;

            fn_mkdir($pack_path);
            fn_put_contents($pack_path . '/schema.json', json_encode($schema));
        }

        return $schema;
    }

    /**
     * Downloads upgrade package from the Upgade server
     *
     * @param  string $connector_id Connector identifier (core, addon_name, seo, some_addon)
     * @return bool   True if upgrade package was successfully downloaded, false otherwise
     */
    public function downloadPackage($connector_id)
    {
        $connectors = $this->getConnectors();

        if (isset($connectors[$connector_id])) {
            $logger = Log::instance($connector_id);
            $logger->add(sprintf('Downloading "%s" upgrade package', $connector_id));

            $schema = $this->getSchema($connector_id);
            $pack_dir = $this->getPackagesDir() . $connector_id . '/';
            $pack_path = $pack_dir . $schema['file'];

            list($result, $message) = $connectors[$connector_id]->downloadPackage($schema, $pack_path);

            if (!empty($message)) {
                $logger->add($message);
                $this->setNotification('W', __('warning'), $message);
            }

            if ($result) {
                fn_mkdir($pack_dir . 'content');
                fn_decompress_files($pack_path, $pack_dir . 'content/');

                list($result, $message) = $this->checkPackagePermissions($connector_id);

                if ($result) {
                    $logger->add('Upgrade package has been downloaded and ready to install');

                    $this->setNotification('N', __('notice'), __('uc_downloaded_and_ready'));
                } else {
                    fn_rm($pack_dir . 'content');
                    fn_rm($pack_path);

                    $this->setNotification('E', __('error'), $message);

                    $logger->add($message);
                }
            }

            return $result;

        } else {
            $this->setNotification('E', __('error'), __('uc_connector_not_found'));

            return false;
        }
    }

    /**
     * Gets extra validators from Upgrade package
     *
     * @param  string $package_id Package id like "core", "access_restrictions"
     * @param  array  $schema     Package schema
     * @return array  Instances of the extra validators
     */
    public function getPackageValidators($package_id, $schema)
    {
        $validators = array();

        if (!empty($schema['validators'])) {
            $validators_path = $this->getPackagesDir() . $package_id . '/content/validators/';

            foreach ($schema['validators'] as $validator_name) {
                if (file_exists($validators_path . $validator_name . '.php')) {
                    include_once $validators_path . $validator_name . '.php';

                    $class_name = "\\Tygh\\UpgradeCenter\\Validators\\" . $validator_name;
                    if (class_exists($class_name)) {
                        $validators[] = new $class_name();
                    }
                }
            }
        }

        return $validators;
    }

    /**
     * Gets list of the files to be updated with the hash checking statuses
     * @param  string $package_id Package id like "core", "access_restrictions"
     * @return array  List of files
     */
    public function getPackageContent($package_id)
    {
        $schema = $this->getSchema($package_id, true);

        if (!empty($schema['files'])) {
            foreach ($schema['files'] as $path => $file_data) {
                $original_path = $this->config['dir']['root'] . '/' . $path;

                switch ($file_data['status']) {
                    case 'changed':
                        if (!file_exists($original_path) || (file_exists($original_path) && md5_file($original_path) != $file_data['hash'])) {
                            $schema['files'][$path]['collision'] = true;
                        }

                        break;

                    case 'deleted':
                        if (file_exists($original_path) && md5_file($original_path) != $file_data['hash']) {
                            $schema['files'][$path]['collision'] = true;
                        }
                        break;

                    case 'new':
                        if (file_exists($original_path)) {
                            $schema['files'][$path]['collision'] = true;
                        }
                        break;
                }
            }
        }

        return $schema;
    }

    /**
     * Validates and installs package
     *
     * @todo Additional migrations validation
     *
     * @param string $package_id Package id like "core", "access_restrictions", etc
     * @return array($result, $data) Installation result
     */
    public function install($package_id, $request)
    {
        $logger = Log::instance($package_id);
        try {
            $error_reporting = error_reporting();
            // Do not display (supress) fatal errors
            error_reporting(E_ALL & ~E_ERROR);

            $this->registerErrorHandlers($logger);
            list ($result, $data) = $this->installUpgradePackage($package_id, $request);
            $this->restoreErrorHandlers();

            // Restore original error reporting level
            error_reporting($error_reporting);

            if (function_exists('opcache_reset')) {
                opcache_reset();
            }

            return array($result, $data);
        } catch (\Exception $e) {
            $logger->add(sprintf('Caught an exception: %s', (string) $e));

            if (function_exists('opcache_reset')) {
                opcache_reset();
            }

            return array(false, array($e->getMessage()));
        }
    }

    protected function registerErrorHandlers(Log $logger)
    {
        // Fatal errors handler
        register_shutdown_function(array($this, 'processShutdownHandler'), $logger);

        // Other errors handler
        set_error_handler(function ($code, $message, $filename, $line) use ($logger) {
            if (error_reporting() & $code) {
                $php_error = new PHPErrorException($message, $code, $filename, $line);
                $php_error = (string) $php_error;
                $logger->add($php_error);
                error_log(addslashes($php_error), 0);

                return true;
            }

            return false;
        });
    }

    /**
     * Logs PHP fatal errors happened during upgrade process to upgrade log.
     *
     * @param \Tygh\UpgradeCenter\Log $logger
     */
    public function processShutdownHandler(Log $logger)
    {
        $error = error_get_last();
        $fatal_error_types = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING);

        if ($error !== null
            && isset($error['type'])
            && in_array($error['type'], $fatal_error_types)
        ) {
            $php_error = new PHPErrorException($error['message'], $error['type'], $error['file'], $error['line']);
            $php_error = (string) $php_error;
            $logger->add($php_error);

            // Fatal errors were supressed, so we should log them manually
            if ($error['type'] == E_ERROR) {
                error_log(addslashes($php_error), 0);
            }
        }
    }

    public function restoreErrorHandlers()
    {
        restore_error_handler();
    }

    protected function installUpgradePackage($package_id, $request)
    {
        $result = true;

        $information_schema = $this->getSchema($package_id, false);

        $logger = Log::instance($package_id);

        $logger->drawHeader()->add(array(
            sprintf('Starting installation of the "%s" upgrade package', $package_id),
            sprintf('Upgrading version %s to %s', $information_schema['from_version'], $information_schema['to_version']),
            sprintf('Running as user "%s"', fn_get_process_owner_name())
        ));

        Output::steps(5); // Validators, Backups (database/files), Copying Files, Migrations, Languages
        Output::display(__('uc_title_validators'), __('uc_upgrade_progress'), false);

        $logger->add('Executing pre-upgrade validators');

        $validators = $this->getValidators();
        $schema = $this->getSchema($package_id, true);

        $package_validators = $this->getPackageValidators($package_id, $schema);

        $logger->add(sprintf('Found %u validators at package', sizeof($package_validators)));

        if (!empty($package_validators)) {
            $validators = array_merge($package_validators, $validators);
        }

        foreach ($validators as $validator) {
            $logger->add(sprintf('Executing "%s" validator', $validator->getName()));
            Output::display(__('uc_execute_validator', array('[validator]' => $validator->getName())), '', false);

            list($result, $data) = $validator->check($schema, $request);
            if (!$result) {
                break;
            }
        }

        if (!$result) {
            $logger->add(sprintf('Upgrade stopped: awaiting resolving "%s" validator errors', $validator->getName()));

            return array($result, array($validator->getName() => $data));
        } else {
            $result = self::PACKAGE_INSTALL_RESULT_SUCCESS;
            if ($this->perform_backup) {
                $backup_filename
                    = "upg_{$package_id}_{$information_schema['from_version']}-{$information_schema['to_version']}_" .
                      date('dMY_His', TIME);
                $logger->add(sprintf('Backup filename is "%s"', $backup_filename));

                // Prepare restore.php file. Paste necessary data and access information
                $restore_preparation_result = $this->prepareRestore(
                    $package_id, $schema, $information_schema, $backup_filename . '.zip'
                );

                if (!$restore_preparation_result) {
                    $logger->add('Upgrade stopped: unable to prepare restore file.');

                    return array(false, array(__('restore') => __('upgrade_center.error_unable_to_prepare_restore')));
                }

                list($restore_key, $restore_file_path, $restore_http_path) = $restore_preparation_result;
            } else {
                $logger->add('Files and database backup skipped');
            }

            $content_path = $this->getPackagesDir() . $package_id . '/content/';

            // Run pre script
            if (!empty($schema['scripts']['pre'])) {
                $pre_script_file_path = $content_path . 'scripts/' . $schema['scripts']['pre'];
                $logger->add(sprintf('Executing pre-upgrade script "%s"', $pre_script_file_path));

                include_once $pre_script_file_path;

                $logger->add('Pre-upgrade script executed successfully');
            }

            $logger->add('Closing storefront');
            $this->closeStore();

            // Collect email recipients for notifications
            $email_recipients = array();

            $user_data = fn_get_user_short_info(\Tygh::$app['session']['auth']['user_id']);
            if (!empty($user_data['email'])) {
                $email_recipients[] = $user_data['email'];
            }

            $user_is_root_admin = isset(\Tygh::$app['session']['auth']['is_root']) && \Tygh::$app['session']['auth']['is_root'] == 'Y';
            if (!$user_is_root_admin) {
                $root_admin_id = db_get_field(
                    "SELECT user_id FROM ?:users WHERE company_id = 0 AND is_root = 'Y' AND user_type = 'A'"
                );
                $root_admin_data = fn_get_user_short_info($root_admin_id);

                if (!empty($root_admin_data['email'])) {
                    $email_recipients[] = $root_admin_data['email'];
                }
            }

            if ($this->perform_backup) {
                fn_set_storage_data('collisions_hash', null);

                $logger->add('Backing up files and database');
                Output::display(__('backup_data'), '', true);

                $backup_file = DataKeeper::backup(array(
                    'pack_name' => $backup_filename,
                    'compress' => 'zip',
                    'set_comet_steps' => false,
                    'move_progress' => false,
                    'extra_folders' => array(
                        'var/langs'
                    )
                ));
                if (empty($backup_file) || !file_exists($backup_file)) {
                    $logger->add('Upgrade stopped: failed to backup DB/files');

                    return array(false, array(__('backup') => __('text_uc_failed_to_backup_tables')));
                }

                $logger->add(sprintf('Backup created at "%s"', $backup_file));

                $email_data = array(
                    'backup_file' => $backup_file,
                    'settings_section_url' => fn_url('settings.manage'),
                    'restore_link' => "{$restore_http_path}?uak={$restore_key}",
                );
            } else {
                $email_data = array(
                    'settings_section_url' => fn_url('settings.manage'),
                );
            }

            // Send mail to admin e-mail with information about backup
            $logger->add(sprintf('Sending upgrade information e-mail to: %s', implode(', ', $email_recipients)));

            $mail_sent = Mailer::sendMail(array(
                'to' => $email_recipients,
                'from' => 'default_company_site_administrator',
                'data' => $email_data,
                'tpl' => 'upgrade/backup_info.tpl',
            ), 'A', Registry::get('settings.Appearance.backend_default_language'));

            if ($mail_sent) {
                $logger->add('E-mail was successfully sent');
            } else {
                $logger->add('Failed to send e-mail');

                return array(false, array());
            }

            Output::display(__('uc_copy_files'), '', true);

            // Move files from package
            $logger->add('Copying package files');
            $this->applyPackageFiles($content_path . 'package', $this->config['dir']['root']);

            $logger->add('Deleting files removed at new version');
            $this->cleanupOldFiles($schema);

            // Copy files from themes_repository to design folder
            $logger->add('Processing themes files');
            $this->processThemesFiles($schema);

            Output::display(__('uc_run_migrations'), '', true);

            // Run migrations
            if (empty($schema['migrations'])) {
                $logger->add('No migrations found at package');
            } else {
                $logger->add(sprintf('Executing %u migrations found at package', sizeof($schema['migrations'])));

                $minimal_date = 0;

                foreach ($schema['migrations'] as $migration) {
                    preg_match('/^[0-9]+/', $migration, $matches);

                    if (!empty($matches[0])) {
                        $date = $matches[0];
                        if ($date < $minimal_date || empty($minimal_date)) {
                            $minimal_date = $date;
                        }
                    }
                }

                $config = array(
                    'migration_dir' => realpath($content_path . 'migrations/'),
                    'package_id' => $package_id,
                );

                try {
                    $migration_succeed = Migration::instance($config)->migrate($minimal_date);
                } catch (DatabaseException $e) {
                    // Find out which migration caused an exception using its trace
                    $failed_migration_file = null;

                    // DatabaseException could be thrown as a replacement of original exception,
                    // in this case we should look through original's exception trace
                    $exception_with_trace = $e->getPrevious() ?: $e;

                    foreach ($exception_with_trace->getTrace() as $trace) {
                        if (isset($trace['file']) && strpos($trace['file'], $config['migration_dir']) === 0) {
                            $failed_migration_file = basename($trace['file']);
                            break;
                        }
                    }

                    $this->setNotification('E', __('error'), __('uc_migration_failed', array('[migration]' => $failed_migration_file)));

                    $migration_succeed = false;
                    $logger->add((string) $e);
                }

                if ($migration_succeed) {
                    $logger->add('Migrations were executed successfully');
                } else {
                    $result = self::PACKAGE_INSTALL_RESULT_WITH_ERRORS;
                    $logger->add('Failed to execute migrations');
                }
            }

            Output::display(__('uc_install_languages'), '', true);
            list ($lang_codes_to_install, $failed_lang_codes) = $this->installLanguages($schema, $logger, $content_path);

            if (!empty($lang_codes_to_install) && !empty($failed_lang_codes)) {
                $result = self::PACKAGE_INSTALL_RESULT_WITH_ERRORS;
                $logger->add(sprintf('Failed to install languages: %s', implode(', ', $failed_lang_codes)));
            }
        }

        $upgrade_schema = $this->getSchema($package_id);

        // Run post script
        if (!empty($schema['scripts']['post'])) {
            $post_script_file_path = $content_path . 'scripts/' . $schema['scripts']['post'];

            $logger->add(sprintf('Executing post-upgrade script "%s"', $post_script_file_path));

            $upgrade_notes = array();

            include_once $post_script_file_path;

            $logger->add('Post-upgrade script executed successfully');

            $upgrade_notification_text = '';
            foreach ($upgrade_notes as $note) {
                $delim = false;
                if (!empty($note['title'])) {
                    $upgrade_notification_text .= "<h3>{$note['title']}</h3>";
                    $delim = true;
                }
                if (!empty($note['message'])) {
                    $upgrade_notification_text .= "<div>{$note['message']}</div>";
                    $delim = true;
                }
                if ($delim) {
                    $upgrade_notification_text .= "<hr>";
                }
            }

            if ($upgrade_notification_text) {
                $upgrade_notification_title = __('upgrade_notification_title', array(
                    '[product]' => PRODUCT_NAME,
                    '[version]' => $upgrade_schema['to_version']
                ));

                $logger->add(sprintf('Sending upgrade information e-mail to: %s', implode(', ', $email_recipients)));
                $mail_sent = Mailer::sendMail(array(
                    'to' => $email_recipients,
                    'from' => 'default_company_site_administrator',
                    'data' => array(),
                    'subj' => $upgrade_notification_title,
                    'body' => $upgrade_notification_text
                ), 'A', Registry::get('settings.Appearance.backend_default_language'));
                if ($mail_sent) {
                    $logger->add('Upgrade information e-mail was successfully sent');
                } else {
                    $logger->add('Failed to send e-mail');
                }

                fn_set_notification(
                    'I',
                    $upgrade_notification_title,
                    __('upgrade_notification_message') . $upgrade_notification_text,
                    'S'
                );
            }
        }

        // Clear obsolete files
        $logger->add('Cleaning cache');
        fn_clear_cache();
        fn_clear_template_cache();

        // Add information to "Installed upgrades section"
        $logger->add('Saving upgrade information to DB');
        $this->storeInstalledUpgrade($upgrade_schema);

        // Collect statistic data
        $logger->add('Sending statistics');
        Http::get(
            Registry::get('config.resources.updates_server') . '/index.php?dispatch=product_updates.updated',
            $this->getStatsData($package_id),
            array('timeout' => 10)
        );

        $this->onSuccessPackageInstall($package_id, $schema, $information_schema);

        $logger->add('Deleting package contents');
        $this->deletePackage($package_id);

        Output::display(__('text_uc_upgrade_completed'), '', true);
        $logger->add('Upgrade completed!');

        return array($result, array());
    }

    /**
     * Deletes schema and package content of the upgrade package
     *
     * @param  string $package_id Package identifier
     * @return bool   true if deleted
     */
    public function deletePackage($package_id)
    {
        $pack_dir = $this->getPackagesDir() . $package_id . '/';

        return fn_rm($pack_dir);
    }

    protected function onSuccessPackageInstall($package_id, $content_schema, $information_schema)
    {
        $connectors = $this->getConnectors();

        if (isset($connectors[$package_id])) {
            $connector = $connectors[$package_id];

            if (method_exists($connector, 'onSuccessPackageInstall')) {
                call_user_func(
                    array($connector, 'onSuccessPackageInstall'),
                    $content_schema, $information_schema
                );
            }
        }
    }

    /**
     * Unpacks and checks the uploaded upgrade pack
     *
     * @param  string $path Path to the zip/tgz archive with the upgrade
     * @return true   if upgrade pack is ready to use, false otherwise
     */
    public function uploadUpgradePack($pack_info)
    {
        // Extract the add-on pack and check the permissions
        $extract_path = fn_get_cache_path(false) . 'tmp/upgrade_pack/';
        $destination = $this->getPackagesDir();

        // Re-create source folder
        fn_rm($extract_path);
        fn_mkdir($extract_path);

        fn_copy($pack_info['path'], $extract_path . $pack_info['name']);

        if (fn_decompress_files($extract_path . $pack_info['name'], $extract_path)) {
            if (file_exists($extract_path . 'schema.json')) {
                $schema = json_decode(fn_get_contents($extract_path . 'schema.json'), true);

                if ($this->validateSchema($schema)) {
                    $package_id = preg_replace('/\.(zip|tgz|gz)$/i', '', $pack_info['name']);

                    $this->deletePackage($package_id);
                    fn_mkdir($destination . $package_id);

                    fn_copy($extract_path, $destination . $package_id);
                    list($result, $message) = $this->checkPackagePermissions($package_id);

                    if ($result) {
                        $this->setNotification('N', __('notice'), __('uc_downloaded_and_ready'));
                    } else {
                        $this->setNotification('E', __('error'), $message);
                        $this->deletePackage($package_id);
                    }

                } else {
                    $this->setNotification('E', __('error'), __('uc_broken_upgrade_connector', array('[connector_id]' => $pack_info['name'])));
                }
            } else {
                $this->setNotification('E', __('error'), __('uc_unable_to_read_schema'));
            }
        }

        // Clear obsolete unpacked data
        fn_rm($extract_path);

        return false;
    }

    /**
     * Prepares restore.php file.
     *
     * @return bool if all necessary information was added to restore.php
     */
    protected function prepareRestore($package_id, $content_schema, $information_schema, $backup_filename)
    {
        $logger = Log::instance($package_id);
        $logger->add('Preparing restore script');

        $upgrades_dir = $this->config['dir']['root'] . '/upgrades';
        $source_restore_file_path = $upgrades_dir . '/source_restore.php';

        $target_restore_dir_name = "{$package_id}_{$information_schema['from_version']}-{$information_schema['to_version']}";
        $target_restore_file_name = 'restore_' . date('Y-m-d_H-i-s', TIME) . '.php';

        $target_restore_dir_path = $upgrades_dir . "/{$target_restore_dir_name}/";
        $target_restore_file_path = $target_restore_dir_path . $target_restore_file_name;

        $target_restore_http_path = Registry::get('config.http_location') . "/upgrades/{$target_restore_dir_name}/{$target_restore_file_name}";

        $target_restore_dir_perms = 0755;
        $target_restore_file_perms = 0644;

        if (is_dir($upgrades_dir)) {
            $logger->add(sprintf('Upgrades directory permissions: %s', fn_get_file_perms_info($upgrades_dir)));
        } else {
            $logger->add(sprintf('Upgrades directory not found at "%s"', $upgrades_dir));

            return false;
        }

        if (file_exists($source_restore_file_path)) {
            $logger->add(sprintf('Source restore script permissions: %s', fn_get_file_perms_info($source_restore_file_path)));

            if (!is_readable($source_restore_file_path)) {
                $logger->add('Source restore script is not readable');

                return false;
            }
        } else {
            $logger->add(sprintf('Source restore script not found at "%s"', $source_restore_file_path));

            return false;
        }

        if (fn_mkdir($target_restore_dir_path, $target_restore_dir_perms)) {
            $logger->add(array(
                sprintf('Created directory for restore script at "%s"', $target_restore_dir_path),
                sprintf('Directory permissions: %s', fn_get_file_perms_info($target_restore_dir_path))
            ));
        } else {
            $logger->add(sprintf('Unable to create directory for restore script at "%s"', $target_restore_dir_path));

            return false;
        }

        $content = fn_get_contents($source_restore_file_path);

        $restore_key = md5(uniqid()) . md5(uniqid('', true));

        $stats_data = $this->getStatsData($package_id);

        $restore_data = array(
            'backup' => array(
                'filename' => $backup_filename,
                'created_at' => date('Y-m-d H:i:s', TIME),
                'created_on_version' => PRODUCT_VERSION
            )
        );

        $content = str_replace(
            array(
                "'%UC_SETTINGS%'",
                "'%CONFIG%'",
                "'%BACKUP_FILENAME%'",
                "'%RESTORE_KEY%'",
                "'%STATS_DATA%'",
                "'%RESTORE_DATA%'",
            ),
            array(
                var_export($this->settings, true),
                var_export(Registry::get('config'), true),
                var_export($backup_filename, true),
                var_export($restore_key, true),
                var_export($stats_data, true),
                var_export($restore_data, true),
            ),
            $content
        );

        if (fn_put_contents($target_restore_file_path, $content, '', $target_restore_file_perms)) {
            $logger->add(array(
                sprintf('Created restore script at "%s"', $target_restore_file_path),
                sprintf('Restore script permissions: %s', fn_get_file_perms_info($target_restore_file_path)),
            ));
        } else {
            $logger->add(sprintf('Unable to create restore script at "%s"', $target_restore_file_path));

            return false;
        }

        // Ensure that target restore script directory has correct permissions (0755)
        $logger->add('Correcting target restore script directory permissions...');
        $this->chmod($target_restore_dir_path, $target_restore_dir_perms, $logger);
        $logger->add(sprintf('Target restore script directory permissions: %s', fn_get_file_perms_info($target_restore_dir_path)));


        // Restore validator could change permissions for upgrades directory to "0777" if it wasn't writable.
        // "0777" are not acceptable permissions for that directory because some servers restrict execution of
        // PHP scripts located at directory with "0777" permissions.
        $logger->add('Correcting upgrades directory permissions...');
        $this->chmod($upgrades_dir, $target_restore_dir_perms, $logger);
        $logger->add(sprintf('Upgrades directory permissions: %s', fn_get_file_perms_info($upgrades_dir)));

        // Check if restore is available through the HTTP
        $logger->add('Checking restore script availability via HTTP');
        $result = Http::get($target_restore_http_path);

        $http_error = Http::getError();
        if (!empty($http_error)) {
            $logger->add(sprintf('HTTP error: %s', $http_error));
        }

        if ($result != 'Access denied') {
            $logger->add(sprintf('Restore script is NOT available via HTTP at "%s".', $target_restore_http_path));

            return false;
        }

        return array($restore_key, $target_restore_file_path, $target_restore_http_path);
    }

    public function chmod($path, $permissions, Log $logger)
    {
        $logger->add(str_repeat('-', 10));
        $logger->add(sprintf('Changing permissions of "%s" to %o', $path, $permissions));
        $logger->lineStart('Using chmod()... ');
        $result = @chmod($path, $permissions);
        $logger->lineEnd($result ? 'OK' : 'FAILED');

        if (!$result) {
            $logger->add('Using FTP...');

            $ftp_connection = Registry::get('ftp_connection');
            if (is_resource($ftp_connection)) {
                $logger->add('Connection is already established');
                $ftp_ready = true;
            } elseif (fn_ftp_connect($this->settings, true)) {
                $logger->add('Connection established');
                $ftp_ready = true;
            } else {
                $logger->add('Failed to establish connection');
                $ftp_ready = false;
            }

            if ($ftp_ready) {
                $result = fn_ftp_chmod_file($path, $permissions, false);
                $logger->add(sprintf('FTP chmod result: %s', $result ? 'OK' : 'FAILED'));
            }
        }
        $logger->add(str_repeat('-', 10));

        return $result;
    }

    protected function getStatsData($package_id)
    {
        $schema = $this->getSchema($package_id);
        $upgrade_package_id = isset($schema['package_id']) ? $schema['package_id'] : null;

        return array(
            'license_number' => $this->settings['license_number'],
            'edition' => PRODUCT_EDITION,
            'ver' => PRODUCT_VERSION,
            'product_build' => PRODUCT_BUILD,
            'package_id' => $upgrade_package_id,
            'admin_uri' => Registry::get('config.http_location'),
        );
    }

    /**
     * Gets list of the available Upgrade Validators
     * @todo Extends by add-ons
     *
     * @return array List of validator objects
     */
    protected function getValidators()
    {
        $validators = array();
        $validator_names = fn_get_dir_contents($this->config['dir']['root'] . '/app/Tygh/UpgradeCenter/Validators/', false, true);

        foreach ($validator_names as $validator) {
            $validator_class = "\\Tygh\\UpgradeCenter\\Validators\\" . fn_camelize(basename($validator, '.php'));

            if (class_exists($validator_class)) {
                $validators[] = new $validator_class;
            }
        }

        return $validators;
    }

    /**
     * Gets list of the available Upgrade Connectors
     *
     * @return array List of connector objects
     */
    protected function getConnectors()
    {
        if (empty($this->connectors)) {
            $connector = new Connectors\Core\Connector();
            $this->connectors['core'] = $connector;

            // Extend connectors by addons
            $addons = Registry::get('addons');

            foreach ($addons as $addon_name => $settings) {
                $class_name =  "\\Tygh\\UpgradeCenter\\Connectors\\" . fn_camelize($addon_name) . "\\Connector";
                $connector = class_exists($class_name) ? new $class_name() : null;

                if (!is_null($connector)) {
                    $this->connectors[$addon_name] = $connector;
                }
            }
        }

        return $this->connectors;
    }

    /**
     * Gets JSON schema of upgrade package as array
     *
     * @param  string $package_id Package id like "core", "access_restrictions"
     * @return array|ContentSchema  Schema data. Empty if schema is not available
     */
    protected function getSchema($package_id, $for_content = false)
    {
        $schema = array();
        if ($for_content) {
            $schema_path = 'content/package.json';
        } else {
            $schema_path = 'schema.json';
        }

        $pack_path = $this->getPackagesDir() . $package_id . '/' . $schema_path;

        if (file_exists($pack_path)) {
            $schema = json_decode(fn_get_contents($pack_path), true);
            $schema['type'] = empty($schema['type']) ? 'hotfix' : $schema['type'];
        }

        if ($for_content && $schema) {
            $schema = new ContentSchema($schema, $this->config);
        }

        return $schema;
    }

    /**
     * Checks if package has rights to update files and if all files were mentioned in the package.json schema
     * @todo Bad codestyle: Multi returns.
     *
     * @param  string $package_id Package id like "core", "access_restrictions", etc
     * @return bool   true if package is correct, false otherwise
     */
    protected function checkPackagePermissions($package_id)
    {
        $content_path = $this->getPackagesDir() . $package_id . '/content/';
        $schema = $this->getSchema($package_id);

        if (empty($schema)) {
            return array(false, __('uc_unable_to_read_schema'));
        }

        if (!file_exists($content_path .'package.json')) {
            return array(false, __('uc_package_schema_not_found'));
        }

        $package_schema = $this->getSchema($package_id, true);

        if (empty($package_schema)) {
            return array(false, __('uc_package_schema_is_not_json'));
        }

        if ($schema['type'] == 'addon') {
            $valid_paths = array(
                'app/addons/' . $package_id,
                'js/addons/' . $package_id,
                'images/',

                'design/backend/css/addons/' . $package_id,
                'design/backend/mail/templates/addons/' . $package_id,
                'design/backend/media/fonts/addons/' . $package_id,
                'design/backend/media/images/addons/' . $package_id,
                'design/backend/templates/addons/' . $package_id,

                'var/themes_repository/[^/]+/css/addons/' . $package_id,
                'var/themes_repository/[^/]+/mail/media/',
                'var/themes_repository/[^/]+/mail/templates/addons/' . $package_id,
                'var/themes_repository/[^/]+/media/fonts/',
                'var/themes_repository/[^/]+/media/images/addons/' . $package_id,
                'var/themes_repository/[^/]+/media/images/addons/' . $package_id,
                'var/themes_repository/[^/]+/styles/data/',
                'var/themes_repository/[^/]+/templates/addons/' . $package_id,

                'var/langs/',
            );

            if (!empty($package_schema['files'])) {
                foreach ($package_schema['files'] as $path => $data) {
                    $valid = false;

                    foreach ($valid_paths as $valid_path) {
                        if (preg_match('#^' . $valid_path . '#', $path)) {
                            $valid = true;
                            break;
                        }
                    }

                    if (!$valid) {
                        return array(false, __('uc_addon_package_forbidden_path', array('[path]' => $path)));
                    }
                }
            }
        }

        // Check migrations
        $migrations = fn_get_dir_contents($content_path . 'migrations/', false, true, '' , '', true);
        $schema_migrations = empty($package_schema['migrations']) ? array() : $package_schema['migrations'];

        if (count($migrations) != count($schema_migrations) || array_diff($migrations, $schema_migrations)) {
            return array(false, __('uc_addon_package_migrations_forbidden'));
        }

        // Check languages
        $languages = fn_get_dir_contents($content_path . 'languages/', true);
        $schema_languages = empty($package_schema['languages']) ? array() : $package_schema['languages'];

        if (count($languages) != count($schema_languages) || array_diff($languages, $schema_languages)) {
            return array(false, __('uc_addon_package_languages_forbidden'));
        }

        // Check files
        $files = array_flip(fn_get_dir_contents($content_path . 'package/', false, true, '' , '', true));
        $schema_files = empty($package_schema['files']) ? array() : $package_schema['files'];

        $diff = array_diff_key($schema_files, $files);
        foreach ($diff as $file) {
            if (!empty($file['status']) && $file['status'] == 'deleted') {
                continue;
            } else {
                return array(false, __('uc_addon_package_files_do_not_match_schema'));
            }
        }

        // Check pre/post scripts
        if (!empty($package_schema['scripts'])) {
            $scripts = fn_get_dir_contents($content_path . 'scripts/', false, true);
            $schema_scripts = array();
            if (!empty($package_schema['scripts']['pre'])) {
                $schema_scripts[] = $package_schema['scripts']['pre'];
            }
            if (!empty($package_schema['scripts']['post'])) {
                $schema_scripts[] = $package_schema['scripts']['post'];
            }

            if (count($scripts) != count($schema_scripts) || array_diff($scripts, $schema_scripts)) {
                return array(false, __('uc_addon_package_pre_post_scripts_mismatch'));
            }
        }

        return array(true, '');
    }

    /**
     * Validates schema to check if upgrade pack can be applied
     *
     * @param  array $schema Pack schema data
     * @return bool  true if valid, false otherwise
     */
    protected function validateSchema($schema)
    {
        $is_valid = true;

        $required_fields = array(
            'file',
            'name',
            'description',
            'from_version',
            'to_version',
            'timestamp',
            'size',
            'type'
        );

        foreach ($required_fields as $field) {
            if (empty($schema[$field])) {
                $is_valid = false;
            }
        }

        if ($is_valid) {
            switch ($schema['type']) {
                case 'core':
                case 'hotfix':
                    if ($schema['from_version'] != PRODUCT_VERSION) {
                        $is_valid = false;
                    }
                    break;

                case 'addon':
                    $addon_scheme = SchemesManager::getScheme($schema['id']);

                    if (!empty($addon_scheme)) {
                        $addon_version = $addon_scheme->getVersion();
                    } else {
                        $is_valid = false;
                        break;
                    }

                    if ($schema['from_version'] != $addon_version) {
                        $is_valid = false;
                    }
                    break;
            }
        }

        return $is_valid;
    }

    /**
     * Copies package files to the core
     * @todo Make console coping
     *
     * @param  string $from Source direcory with files
     * @param  string $to   Destination directory
     * @return bool   true if copied, false otherwise
     */
    protected function applyPackageFiles($from, $to)
    {
        if (is_dir($from)) {
            $result = fn_copy($from, $to);
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Cleanups old files mentioned in upgrade schema
     *
     * @param array $schema Upgrade package schema
     */
    protected function cleanupOldFiles($schema)
    {
        foreach ($schema['files'] as $file_path => $file) {
            if ($file['status'] == 'deleted') {
                fn_rm($this->config['dir']['root'] . '/' . $file_path);
            }
        }
    }

    /**
     * Copies theme files from the theme_repository to design folder
     *
     * @param  array|ContentSchema $schema UC package schema
     * @return array List of processed files
     */
    protected function processThemesFiles($schema)
    {
        if (empty($schema['files'])) {
            return array();
        }

        $themes_files = $schema->getThemesFiles();

        if (!empty($themes_files)) {
            foreach ($themes_files as $file_path => $file_data) {
                if ($file_data['status'] === 'deleted') {
                    fn_rm($this->config['dir']['root'] . '/' . $file_path);
                } else {
                    $dir_path = dirname($this->config['dir']['root'] . '/' . $file_path);
                    fn_mkdir($dir_path);
                    fn_copy($this->config['dir']['root'] . '/' . $file_data['source'], $this->config['dir']['root'] . '/' . $file_path);
                }
            }
        }

        return $themes_files;
    }

    /**
     * Gets full path to the packages dir
     * @return string /full/path/to/packages/dir
     */
    protected function getPackagesDir()
    {
        return $this->config['dir']['upgrade'] . 'packages/';
    }

    /**
     * Closes storefront
     */
    protected function closeStore()
    {
        fn_set_store_mode('closed');
        if (fn_allowed_for('ULTIMATE')) {
            $company_ids = fn_get_all_companies_ids();
            foreach ($company_ids as $company_id) {
                fn_set_store_mode('closed', $company_id);
            }
        }
    }

    protected function storeInstalledUpgrade($schema)
    {
        $files = fn_get_storage_data('collision_files');

        fn_set_storage_data('collision_files', null);
        fn_set_storage_data('collisions_hash', null);

        if (!empty($files)) {
            $files = unserialize($files);
            foreach ($files as $id => $path) {
                $files[$id] = array(
                    'file_path' => $path,
                    'status' => 'C',
                );
            }
            $files = serialize($files);

        } else {
            $files = '';
        }

        $installed_pack = array(
            'type' => $schema['type'],
            'name' => $schema['name'],
            'timestamp' => TIME,
            'description' => $schema['description'],
            'conflicts' => $files,
        );

        db_query('INSERT INTO ?:installed_upgrades ?e', $installed_pack);
    }

    /**
     * Checks if script run from the console
     *
     * @return bool true if run from console
     */
    protected function isConsole()
    {
        if (is_null($this->is_console)) {
            if (defined('CONSOLE')) {
                $this->is_console = true;
            } else {
                $this->is_console = false;
            }
        }

        return $this->is_console;
    }

    /**
     * Returns instance of App
     *
     * @return App
     */
    public static function instance($params = array())
    {
        if (empty(self::$instance)) {
            self::$instance = new self($params);
        }

        return self::$instance;
    }

    public function __construct($params)
    {
        $this->config = Registry::get('config');
        $this->params = $params;
        $this->settings = Settings::instance()->getValues('Upgrade_center');
    }

    /**
     * Retrieves codes of languages, which .PO-files have to be updated by the upgrade package.
     *
     * @param array $package_content_schema Content schema of the upgrade package
     *
     * @return array List of language codes (i.e. array('ru', 'en', 'ua', ...))
     */
    public function getLangCodesToReinstallFromContentSchema($package_content_schema)
    {
        $lang_codes = array();
        $lang_packs_dir_path = fn_get_rel_dir($this->config['dir']['lang_packs']);

        if (isset($package_content_schema['files'])) {
            foreach ($package_content_schema['files'] as $file_path => $file_info) {
                // the file is located at "var/langs" directory,
                // so we should parse language code from file path
                if (strpos($file_path, $lang_packs_dir_path) === 0) {
                    // remove "var/langs" part from file path
                    $file_path = trim(str_replace($lang_packs_dir_path, '', $file_path), '\\/');

                    // first directory of path is the lang code
                    $parent_directories = fn_get_parent_directory_stack($file_path);
                    $lang_code = end($parent_directories);
                    if ($lang_code) {
                        $lang_code = trim($lang_code, '\\/');
                        $lang_codes[$lang_code] = $lang_code;
                    }
                }
            }
        }

        return array_values($lang_codes);
    }

    /**
     * @param array  $package_content_schema Package content schema
     * @param Log    $logger                 Logger instance
     * @param string $package_content_path   Package content path
     *
     * @return array First element is a list of languages to be installed, second element is a list languages failed to install
     */
    public function installLanguages($package_content_schema, Log $logger, $package_content_path)
    {
        $failed_to_install = array();
        $installed_languages = array_keys(Languages::getAvailable('A', true));

        if (empty($package_content_schema['languages'])) {
            $logger->add('Installing languages using upgraded *.po files');
            $po_pack_basepath = $this->config['dir']['lang_packs'];
            $lang_codes_to_install = $this->getLangCodesToReinstallFromContentSchema($package_content_schema);
        } else {
            $logger->add('Installing languages provided by package');
            $po_pack_basepath = $package_content_path . 'languages/';
            $lang_codes_to_install = (array) $package_content_schema['languages'];
        }
        $logger->add(sprintf('Already installed languages: %s', implode(', ', $installed_languages)));
        $logger->add(sprintf('Languages to be installed: %s', implode(', ', $lang_codes_to_install)));

        if (in_array(CART_LANGUAGE, $lang_codes_to_install)) {
            $fallback_lang_code = CART_LANGUAGE;
        } elseif (in_array('en', $lang_codes_to_install)) {
            $fallback_lang_code = 'en';
        } else {
            $fallback_lang_code = null;
        }

        foreach ($installed_languages as $lang_code) {
            $logger->lineStart(sprintf('Installing "%s" language... ', $lang_code));

            if (in_array($lang_code, $lang_codes_to_install)) {
                Output::display(__('install') . ': ' . $lang_code, '', false);

                if (false === Languages::installCrowdinPack($po_pack_basepath . $lang_code, array(
                    'install_newly_added' => true,
                    'validate_lang_code' => $lang_code,
                    'reinstall' => true,
                ))) {
                    $logger->lineEnd('FAILED');
                    $failed_to_install[] = $lang_code;
                } else {
                    $logger->lineEnd('OK');
                }
            } elseif ($fallback_lang_code !== null) {
                if (false === Languages::installCrowdinPack($po_pack_basepath . $fallback_lang_code, array(
                    'reinstall' => true,
                    'force_lang_code' => $lang_code,
                    'install_newly_added' => true,
                ))) {
                    $logger->lineEnd('FAILED');
                    $failed_to_install[] = $lang_code;
                } else {
                    $logger->lineEnd('OK');
                }
            } else {
                $logger->lineEnd('SKIPPED');
            }
        }

        return array($lang_codes_to_install, $failed_to_install);
    }
}
