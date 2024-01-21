<?php
/**
 * 2007-2024 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2024 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Recommandation extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'recommandation';
        $this->tab = 'market_place';
        $this->version = '1.0.0';
        $this->author = 'Mathias KLIEM';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Recommandation');
        $this->description = $this->l('A module to make recommandation for product using an API.');

        $this->confirmUninstall = $this->l('You\'re sure that you wan\'t to delete this awesome module');

        $this->ps_versions_compliancy = array('min' => '8.1', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('RECOMMANDATION_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader'); // &&
//            $this->registerHook('displayRightColumnProduct');
    }

    public function uninstall()
    {
        Configuration::deleteByName('RECOMMANDATION_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */

        $apiError = "";

        if (((bool)Tools::isSubmit('submitRecommandationModule')) == true) {
            $this->postProcess();
        }

        if (((bool)Tools::isSubmit('getRecommandation')) == true) {
            $form_values = $this->getConfigFormValues();

            // We verify that all the values are set

            if (isset($form_values['RECOMMANDATION_API_URL']) && isset($form_values['RECOMMANDATION_LOGIN']) && isset($form_values['RECOMMANDATION_PASSWORD']) && !empty($form_values['RECOMMANDATION_API_URL']) && !empty($form_values['RECOMMANDATION_LOGIN']) && !empty($form_values['RECOMMANDATION_PASSWORD'])) {
                $url = $form_values['RECOMMANDATION_API_URL'];
                $login = $form_values['RECOMMANDATION_LOGIN'];
                $password = $form_values['RECOMMANDATION_PASSWORD'];

                // We verify that the url is valid
                if (filter_var($url, FILTER_VALIDATE_URL) || filter_var($url, FILTER_VALIDATE_IP) || $url == 'localhost') {
                    // We get all the activated products on prestashop

                    $products = Product::getProducts($this->context->language->id, 0, 0, 'id_product', 'ASC', false, true);

                    foreach ($products as $p) {
                        var_dump($p['name']);


                        // We call the API to get the recommandation
                        $ch = curl_init();

                        // We set up the final url

                        $name = str_replace(" ", "%20", $p['name']);


                        $finalUrl = $url . "/recommend/" . $name;

                        var_dump($finalUrl);

                        // We set the url
                        curl_setopt($ch, CURLOPT_URL, $finalUrl);

                        // We set the login and the password
                        curl_setopt($ch, CURLOPT_USERPWD, $login . ":" . $password);

                        // We set the header
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

                        // We set the method
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

                        // Set CURLOPT_RETURNTRANSFER to true
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        // We get the response
                        $response = curl_exec($ch);

                        // Add the recommandation in the product
                        if (!empty($response)) {
                            // Get the array in the response
                            $response = json_decode($response, true);

                            // We verify that the array is not empty
                            if (count($response['recommendations']) > 0) {
                                $recommandation = $response;

                                $product = new Product($p['id_product']);

                                // We get the ids of the recommandation using the name

                                $ids = array();

                                var_dump($recommandation['recommendations']);


                                foreach ($recommandation['recommendations'] as $r) {
                                    $recommandationProduct = Product::searchByName($this->context->language->id, $r);

                                    var_dump($recommandationProduct);

                                    if ($recommandationProduct !== false) {
                                        if (count($recommandationProduct) > 0) {
                                            foreach ($recommandationProduct as $rp) {
                                                $ids[] = $rp['id_product'];
                                            }
                                        }
                                    }
                                }

                                var_dump($ids);
                                $products->addAccessoriesToProduct();

                            }
                        }
                    }

                    die();


                } else {
                    // Add an error message in apiError
                    $apiError = "The URL is not valid";
                }
            } else {
                // Add an error message in apiError
                $apiError = "Please fill all the fields";
            }

//            die();
        }
        $this->context->smarty->assign('apiError', $apiError);

        $this->context->smarty->assign('module_dir', $this->_path);

        $action = $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name]);

        $this->context->smarty->assign('action', $action);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitRecommandationModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-link"></i>',
                        'desc' => $this->l('Enter the URL of the API'),
                        'name' => 'RECOMMANDATION_API_URL',
                        'label' => $this->l('Url'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-user"></i>',
                        'desc' => $this->l('Enter the login to connect to the API'),
                        'name' => 'RECOMMANDATION_LOGIN',
                        'label' => $this->l('Login'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'RECOMMANDATION_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'RECOMMANDATION_API_URL' => Configuration::get('RECOMMANDATION_API_URL', null),
            'RECOMMANDATION_LOGIN' => Configuration::get('RECOMMANDATION_LOGIN', null),
            'RECOMMANDATION_PASSWORD' => Configuration::get('RECOMMANDATION_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

//    public function hookDisplayRightColumnProduct()
//    {
//
//    }
}
