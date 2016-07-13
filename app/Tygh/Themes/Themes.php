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
namespace Tygh\Themes;

use Tygh\Less;
use Tygh\Registry;
use Tygh\BlockManager\Layout;
use Tygh\Settings;
use Tygh\Storage;
use Tygh\Themes\Styles;

class Themes
{
    public static $compiled_less_filename = 'styles.pcl.css';
    public static $less_backup_dirname = '__less_backup';
    public static $css_backup_dirname = '__css_backup';

    private static $instances = array();

    protected $less = null;
    protected $less_reflection = null;
    protected $theme_name = '';
    protected $theme_path = '';
    protected $relative_path = '';
    protected $repo_path = '';
    protected $manifest = array();

    public function __construct($theme_name)
    {
        $this->theme_name = $theme_name;
        $this->theme_path = fn_get_theme_path('[themes]/' . $theme_name, 'C');
        $this->relative_path = fn_get_theme_path('[relative]/' . $theme_name, 'C');
        $this->repo_path = fn_get_theme_path('[repo]/' . $theme_name, 'C');
    }

    /**
     * Convert theme LESS to CSS files
     *
     * @return boalean Result
     */
    public function convertToCss()
    {
        if (!file_exists($this->theme_path . '/' . THEME_MANIFEST)) {
            fn_put_contents($this->theme_path . '/' . THEME_MANIFEST, '');
        }

        if (!is_writable($this->theme_path . '/' . THEME_MANIFEST)) {
            return false;
        }

        $theme_css_path = $this->theme_path . '/css';

        $less_reflection = $this->getLessReflection();

        if (!empty($less_reflection['output']['main'])) {

            $exclude = array(
                'addons', self::$less_backup_dirname, self::$css_backup_dirname
            );

            if (!(
                $this->convertChunkToCss($less_reflection['output']['main'], $theme_css_path)
                && $this->removeLessFiles($theme_css_path, $theme_css_path . '/' . self::$less_backup_dirname, $exclude)
            )) {
                return false;
            }

        }

        if (!empty($less_reflection['output']['addons'])) {
            foreach ($less_reflection['output']['addons'] as $addon_name => $addon_less_output) {
                if (!empty($addon_less_output)) {
                    if (!$this->convertAddonToCss($addon_name, $addon_less_output)) {
                        return false;
                    }
                }
            }
        }

        $manifest = &$this->getManifest();
        $manifest['converted_to_css'] = true;

        return $this->saveManifest();
    }

    /**
     * Precompile addon LESS
     *
     * @param string $addon             Addon name
     * @param string $addon_less_output Addon less output
     *
     * @return boolean Result
     */
    public function convertAddonToCss($addon, $addon_less_output = '')
    {
        $manifest = &$this->getManifest();

        $_temporary_restore_less = false;

        if (!empty($manifest['converted_to_css'])) {
            $_temporary_restore_less = true;
            $this->restoreLess(false);
        }

        if (empty($addon_less_output)) {
            $less_reflection = $this->getLessReflection();
            $addon_less_output = '';
            if (!empty($less_reflection['output']['addons'][$addon])) {
                $addon_less_output = $less_reflection['output']['addons'][$addon];
            }
        }

        if ($_temporary_restore_less) {
            $exclude = array(
                'addons', self::$less_backup_dirname, self::$css_backup_dirname
            );
            $this->removeLessFiles($this->theme_path . '/css', null, $exclude);
            $manifest['converted_to_css'] = true;
            $this->saveManifest();
        }

        $addon_css_path = $this->theme_path . '/css/addons/' . $addon;
        $addon_less_backup_path = $this->theme_path . '/css/' . self::$less_backup_dirname . '/addons/' . $addon;

        if (!(
            $this->convertChunkToCss($addon_less_output, $addon_css_path)
            && $this->removeLessFiles($addon_css_path, $addon_less_backup_path)
        )) {
            return false;
        }

        return true;
    }

    /**
     * Get CSS content from a file
     *
     * @param mixed $filename CSS file name or relative path
     *
     * @return mixed CSS content or false on failure
     */
    public function getCssContents($filename = null)
    {
        if (is_null($filename)) {
            $filename = Themes::$compiled_less_filename;
        }

        return fn_get_contents($this->theme_path . '/css/' . $filename);
    }

    /**
     * Update CSS file
     *
     * @param string $css_file    CSS file name or relative path
     * @param string $css_content CSS content
     *
     * @return boolean Result
     */
    public function updateCssFile($css_file, $css_content)
    {
        return fn_put_contents($this->theme_path . '/css/' . $css_file, $css_content);
    }

    /**
     * Restore LESS files and remove precompiled LESS files
     *
     * @return bolean Result
     */
    public function restoreLess($remove_precompiled_less = true)
    {
        if (!file_exists($this->theme_path . '/' . THEME_MANIFEST)) {
            fn_put_contents($this->theme_path . '/' . THEME_MANIFEST, '');
        }

        if (!is_writable($this->theme_path . '/' . THEME_MANIFEST)) {
            return false;
        }

        $theme_css_path = $this->theme_path . '/css';

        $less_backup_path = $theme_css_path . '/' . self::$less_backup_dirname;

        if (!is_dir($less_backup_path)) {
            return false;
        }

        if (!fn_copy($less_backup_path, $theme_css_path)) {
            return false;
        }

        if ($remove_precompiled_less) {
            $this->removePrecompiledLess();
        }

        $manifest = &$this->getManifest();
        $manifest['converted_to_css'] = false;

        return $this->saveManifest();
    }

    /**
     * Remove precompiled LESS files
     *
     * @return boolean Result
     */
    public function removePrecompiledLess()
    {
        $theme_css_path = $this->theme_path . '/css';

        $exclude = array(
            self::$less_backup_dirname, self::$css_backup_dirname
        );

        $precompiled_files = fn_get_dir_contents(
            $theme_css_path, false, true, self::$compiled_less_filename, '', true, $exclude
        );

        foreach ($precompiled_files as $pcl_file) {

            $pcl_filepath = $theme_css_path . '/' . $pcl_file;
            $css_backup_filepath = $theme_css_path . '/' . self::$css_backup_dirname . '/' . $pcl_file;

            if (!fn_mkdir(dirname($css_backup_filepath)) || !fn_copy($pcl_filepath, $css_backup_filepath)) {
                return false;
            }

            fn_rm($pcl_filepath);
        }

        return true;
    }

    /**
     * Get theme CSS files list
     *
     * @return array CSS files list
     */
    public function getCssFilesList()
    {
        $from = $this->theme_path . '/css';
        $exclude = array('addons', self::$less_backup_dirname, self::$css_backup_dirname);

        $css_files = fn_get_dir_contents($from, false, true, '.css', '', true, $exclude);

        list($active_addons) = fn_get_addons(array('type' => 'active'));

        foreach ($active_addons as $addon_name => $addon) {
            $css_files = array_merge(
                $css_files,
                fn_get_dir_contents($from . "/addons/$addon_name", false, true, '.css', "addons/$addon_name/", true)
            );
        }

        return $css_files;
    }

    /**
     * Get URL to the file with joint theme CSS
     *
     * @return mixed Url or false on failure
     */
    public function getCssUrl()
    {
        $res = $this->fetchFrontendStyles();

        if (!preg_match('/href="([^"]+)"/is', $res, $m)) {
            return false;
        }

        return $m[1];
    }

    /**
     * Get theme manifest information
     *
     * @return array Manifest information
     */
    public function &getManifest()
    {
        if (empty($this->manifest)) {
            if (file_exists($this->theme_path . '/' . THEME_MANIFEST)) {
                $manifest_path = $this->theme_path . '/' . THEME_MANIFEST;

                $ret = json_decode(fn_get_contents($manifest_path), true);
            } elseif (file_exists($this->theme_path . '/' . THEME_MANIFEST_INI)) {
                $ret = parse_ini_file($this->theme_path . '/' . THEME_MANIFEST_INI);
            } else {
                $ret = array();
            }

            if ($ret) {
                $this->manifest = $ret;
            }
        }

        // Backward compatibility
        if (isset($this->manifest['logo'])) {
            $this->manifest['theme'] = $this->manifest['logo'];
        }
        if (empty($this->manifest['mail'])) {
            $this->manifest['mail'] = $this->manifest['theme'];
        }

        return $this->manifest;
    }

    /**
     * @param array $manifest_data Manifest data to set
     */
    public function setManifest($manifest_data)
    {
        $this->manifest = $manifest_data;
    }

    /**
     * Get theme manifest information from Themes repository
     *
     * @return array Manifest information
     */
    public function getRepoManifest()
    {
        $ret = '';

        if (file_exists($this->repo_path . '/' . THEME_MANIFEST)) {
            $manifest_path = $this->repo_path . '/' . THEME_MANIFEST;

            $ret = json_decode(fn_get_contents($manifest_path), true);
        } elseif (file_exists($this->repo_path . '/' . THEME_MANIFEST_INI)) {
            $ret = parse_ini_file($this->repo_path . '/' . THEME_MANIFEST_INI);
        }

        return $ret;
    }

    /**
     * Save theme manifest information
     *
     * @return boolean Result
     */
    public function saveManifest()
    {
        if (empty($this->manifest)) {
            return false;
        }

        return fn_put_contents($this->theme_path . '/' . THEME_MANIFEST, json_encode($this->manifest));
    }

    /**
     * Get theme name
     *
     * @return string Theme name
     */
    public function getThemeName()
    {
        return $this->theme_name;
    }

    /**
     * Get theme path
     *
     * @return string Theme path
     */
    public function getThemePath()
    {
        return $this->theme_path;
    }

    /**
     * @param string $theme_name
     *
     * @return self
     */
    public static function factory($theme_name)
    {
        if (empty(self::$instances[$theme_name])) {
            self::$instances[$theme_name] = new self($theme_name);
        }

        return self::$instances[$theme_name];
    }

    /**
     * Get LESS reflection (information necessary to precompile LESS): LESS import dirs and structured output
     *
     * @return array LESS reflection
     */
    protected function getLessReflection()
    {
        if (empty($this->less_reflection)) {

            $this->fetchFrontendStyles(array('reflect_less' => true));

            $this->less_reflection = json_decode(
                fn_get_contents(fn_get_cache_path(false) . 'less_reflection.json'), true
            );
        }

        return $this->less_reflection;
    }

    /**
     * Fetch frontend styles
     *
     * @param array Params
     *
     * @return string Frontend styles
     */
    protected function fetchFrontendStyles($params = array())
    {
        fn_clear_cache('assets', 'design/');

        $style_id = Registry::get('runtime.layout.style_id');
        if (empty($style_id)) {
            Registry::set('runtime.layout.style_id', Styles::factory($this->theme_name)->getDefault());
        }

        $view = \Tygh::$app['view'];

        $view->setArea('C');

        $view->assign('use_scheme', true);
        $view->assign('include_dropdown', true);

        foreach ($params as $key => $val) {
            $view->assign($key, $val);
        }

        $ret = $view->fetch('common/styles.tpl');

        $view->setArea(AREA);

        return $ret;
    }

    /**
     * Compile chunk of LESS output and save the result in the file
     *
     * @param string $less_output Chunk of LESS output
     * @param string $css_path    The path where the precompiled LESS will be saved
     *
     * @return boolean Result
     */
    protected function convertChunkToCss($less_output, $css_path)
    {
        $less = $this->getLess();

        $less_reflection = $this->getLessReflection();

        $less->setImportDir($less_reflection['import_dirs']);

        Registry::set('runtime.layout', Layout::instance()->getDefault($this->theme_name));

        $from_path = Storage::instance('assets')->getAbsolutePath($this->relative_path . '/css');

        $compiled_less = $less->customCompile($less_output, $from_path, array(), '', 'C');

        $res = fn_put_contents($css_path . '/' . self::$compiled_less_filename, $compiled_less);

        if ($res === false) {
            return false;
        }

        return true;
    }

    /**
     * Remove LESS files
     *
     * @param string $from       The directory the LESS files are removed from
     * @param string $backup_dir Backup directory
     * @param array  $exclude    The list of directories to skip while removing
     *
     * @return boolean Result
     */
    protected function removeLessFiles($from, $backup_dir, $exclude = array())
    {
        $less_files = fn_get_dir_contents($from, false, true, '.less', '', true, $exclude);

        foreach ($less_files as $less_file) {

            if (!empty($backup_dir)) {

                if (!(
                    fn_mkdir(dirname($backup_dir . '/' . $less_file))
                    && fn_copy($from . '/' . $less_file, $backup_dir . '/' . $less_file)
                )) {
                    return false;
                }

            }

            fn_rm($from . '/' . $less_file);
        }

        return true;
    }

    /**
     * Get LESS compiler instance
     *
     * @return object LESS compiler instance
     */
    protected function getLess()
    {
        if ($this->less === null) {
            $this->less = new Less;
        }

        return $this->less;
    }

    /**
     * Gets theme setting overrides
     *
     * @param  string $lang_code 2-letter language code
     * @return array  Theme setting overrides
     */
    public function getSettingsOverrides($lang_code = CART_LANGUAGE)
    {
        $manifest = &$this->getManifest();

        $settings = array();

        if (!empty($manifest['settings_overrides'])) {
            $settings_overrides = $manifest['settings_overrides'];
            foreach ($settings_overrides as $section_name => $setting_group) {
                $section = Settings::instance()->getSectionByName($section_name);
                if ($section) {
                    $settings[$section_name] = array(
                        'name' => Settings::instance()->getSectionName($section['section_id']),
                        'settings' => array()
                    );

                    foreach ($setting_group as $setting_name => $setting_value) {
                        $setting = Settings::instance()->getSettingDataByName($setting_name, null, $lang_code);
                        if ($setting) {
                            if (is_bool($setting_value)) {
                                $setting_value = $setting_value ? 'Y' : 'N';
                            }
                            $settings[$section_name]['settings'][$setting_name] = array(
                                'object_id' => $setting['object_id'],
                                'name' => $setting['description'],
                                'value' => $setting_value
                            );
                        }
                    }
                }
            }
        }

        return $settings;
    }

    /**
     * Overrides settings values from theme manifest file
     *
     * @param array $settings   Settings to set
     * @param int   $company_id Company identifier
     */
    public function overrideSettings($settings = null, $company_id = null)
    {
        if (is_null($settings)) {
            $settings = array();

            $theme_settings = $this->getSettingsOverrides();
            foreach ($theme_settings as $section_data) {
                foreach ($section_data['settings'] as $setting) {
                    $settings[$setting['object_id']] = $setting['value'];
                }
            }
        }

        foreach ($settings as $object_id => $value) {
            Settings::instance($company_id)->updateValueById($object_id, $value, $company_id);
        }
    }

    /**
     * Creates a clone of the theme.
     *
     * @param string $clone_name Name of the new theme
     * @param array  $clone_data Array with "title" and "description" fields for the new theme
     * @param int    $company_id ID of the owner company for the new theme
     *
     * @return bool Whether cloning has succeed
     */
    public function cloneAs($clone_name, $clone_data = array(), $company_id = 0)
    {
        $cloned = new self($clone_name);

        if (file_exists($cloned->getThemePath())) {
            fn_set_notification('W', __('warning'), __('warning_theme_clone_dir_exists'));

            return false;
        }

        if (!fn_install_theme_files($this->getThemeName(), $cloned->getThemeName(), false)) {
            return false;
        }

        // Layouts that belong to the theme
        $layouts = Layout::instance()->getList(array('theme_name' => $this->getThemeName()));

        // Clone layouts one by one
        foreach ($layouts as $layout) {
            $src_layout_id = $layout['layout_id'];
            unset($layout['layout_id']);
            $layout['theme_name'] = $cloned->getThemeName();
            $layout['from_layout_id'] = $src_layout_id;

            $dst_layout_id = Layout::instance()->update($layout);

            if (!empty($layout['is_default'])) {
                // Re-init layout data
                fn_init_layout(array('s_layout' => $dst_layout_id));
            }
        }

        $manifest = $cloned->getManifest();
        if (isset($clone_data['title'])) {
            $manifest['title'] = $clone_data['title'];
        }
        if (isset($clone_data['description'])) {
            $manifest['description'] = $clone_data['description'];
        }

        // Put logos of current layout to manifest
        $logos = fn_get_logos(Registry::get('runtime.company_id'));
        foreach ($logos as $type => $logo) {
            if (!empty($logo['image'])) {
                $filename = fn_basename($logo['image']['relative_path']);
                Storage::instance('images')->export(
                    $logo['image']['relative_path'],
                    $cloned->getThemePath() . '/media/images/' . $filename
                );
                $manifest[$type] = 'media/images/' . $filename;
            }
        }

        $cloned->setManifest($manifest);
        $cloned->saveManifest();

        fn_install_theme($cloned->getThemeName(), $company_id, false);
        $cloned->overrideSettings(null, $company_id);
    }
}
