{*<!-- {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!--tpl-Products-Tree-Product-->
	<div class="m-0 p-3 col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 box-shadow product js-cart-item"
		data-id="{\App\Purifier::encodeHTML($CRM_ID)}"
		data-qtyinstock="{$RECORD->getRawValue('qtyinstock')}"
		data-amount-in-shopping-cart="{$RECORD->getRawValue('amountInShoppingCart')}"
		data-price-netto="{$RECORD->getRawValue('unit_price')}"
		data-price-gross="{$RECORD->getRawValue('unit_gross')}">
		<div class="col-12 px-0 mb-4">
			<div class="product-image-contener text-center">
				{assign var="IMAGES" value=$RECORD->get('imagename')}
				{if empty($IMAGES) }
					<div class="product-no-image m-auto">
						<span class="fa-stack fa-2x product-no-image">
							<i class="fas fa-camera fa-stack-1x"></i>
							<i class="fas fa-ban fa-stack-2x"></i>
						</span>
					</div>
				{else}
					<div class="product-no-image m-auto" title="{$RECORD->getDisplayValue('productname')}">
						{$RECORD->getModuleModel()->getFieldModel('imagename')->getImg($IMAGES[0])}
					</div>
				{/if}
			</div>
		</div>
		<div class="row mb-2">
			<div class="col-12 fs-100 font-weight-bold"><a href="index.php?module=Products&view=Preview&record={$RECORD->getId()}">{$RECORD->getDisplayValue('productname')}</a></div>
			<div class="col-12 fs-80 text-muted">EAN: {$RECORD->getDisplayValue('ean')}</div>
		</div>
		<div class="row price-content">
			<div class="col-6 d-flex align-items-center quantity-content">
				<div class="col-5 px-0 ">
					<button type="button" class="btn btn-success js-add-to-cart u-border-radius mb-0" title="{\App\Language::translate('LBL_ADD_TO_CART', $MODULE_NAME)}" data-js="click">
						<i class="fas fa-cart-plus mr-1"></i>
					</button>
				</div>
				<div class="input-group input-group-sm col-7 d-flex align-items-center px-0 c-cart-quantity">
					<div class="input-group-prepend">
						<button class="btn btn-secondary js-amount-dec mr-2 c-cart-quantity__btn-circle mb-0" type="button">-</button>
					</div>
					<input class="input-group-prepend form-control js-amount text-center c-cart-quantity__input product-input-quantity border" type="text" value="{if $RECORD->has('amountInShoppingCart')}{$RECORD->getDisplayValue('amountInShoppingCart')}{else}1{/if}">
					<div class="input-group-append">
						<button class="btn btn-sm btn-secondary js-amount-inc ml-2 c-cart-quantity__btn-circle mb-0" type="button">+</button>
					</div>
				</div>
			</div>
			<div class="col-6 quantity-content text-right text-secondary">
				<div class="fs-80"><b>{\App\Language::translate('LBL_NET_PRICE', $MODULE_NAME)}:</b> {$RECORD->getDisplayValue('unit_price')}</div>
				<div class="fs-80"><b>{\App\Language::translate('LBL_GROSS_PRICE', $MODULE_NAME)}:</b> {$RECORD->getDisplayValue('unit_gross')}</div>
			</div>
		</div>
	</div>
	<!--/tpl-Products-Tree-Product-->
{/strip}
