{*<!-- {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Field-AdvPercentage -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	<div class="input-group">
		<input name="{$FIELD_MODEL->getName()}" type="text" value="{$FIELD_MODEL->getEditViewDisplayValue($RECORD)}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}" class="input-medium form-control" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Base_Validator_Js.invokeValidation]]" tabindex="{$FIELD_MODEL->getTabIndex()}" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)} data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}' {/if}{if $FIELD_MODEL->isEditableReadOnly()} readonly="readonly" {/if} />
		<span class="input-group-append">
			<span class="input-group-text">%</span>
		</span>
	</div>
	<!-- /tpl-Base-Edit-Field-AdvPercentage -->
{/strip}
