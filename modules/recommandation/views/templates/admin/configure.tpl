{*
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2024 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel">
    <h3>{l s='Get recommandations' mod='recommandation'}</h3>
    <p>
    <h4>{l s='Here you can ask to have new recommandation for all your product.' mod='recommandation'}</h4><br/>
    <h5>{l s='If the API get 1 or more recommandations, the module add it to the catalogue.' mod='recommandation'}</h5>
    <form action="{$action}" method="post">
        <button type="submit" class="btn btn-primary" id="getRecommandation"
                name="getRecommandation">{l s='Get recommandations' mod='recommandation'}</button>
    </form>
    <div class="alert alert-danger apiError" role="alert">
        {$apiError}
    </div>
    </p>
</div>


<div class="panel">
    <h3>{l s='Configure the API' mod='recommandation'}</h3>
    <p>
    <h4>{l s='Here you can configure the url and the login to access the recommadation API.' mod='recommandation'}</h4>
    <br/>
    </p>
</div>

<script>
    let apiError = document.querySelector(".apiError")

    if (apiError.innerText === ""){
        apiError.style.display = "none"
    } else {
        apiError.style.display = "block"
    }
</script>
