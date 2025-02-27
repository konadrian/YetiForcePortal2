{*<!-- {[The file is published on the basis of YetiForce Public License 4.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Form -->
	<form class="form-horizontal recordEditView js-edit-view-form" name="EditView" method="post" action="index.php" enctype="multipart/form-data" data-js="container">
		<input type="hidden" name="module" value="{$MODULE_NAME}">
		<input type="hidden" name="action" value="Save">
		<input type="hidden" name="record" id="recordId" value="{$RECORD->getId()}">
		<input type="hidden" name="_fromView" value="{\App\Purifier::encodeHtml($ACTION_NAME)}">
		{if isset($RELATION_OPERATION)}
			<input type="hidden" name="relationOperation" value="true">
			<input type="hidden" name="relationId" value="{\App\Purifier::encodeHtml($RELATION_ID)}">
		{/if}
		{if isset($SOURCE_MODULE)}
			<input type="hidden" name="sourceModule" value="{\App\Purifier::encodeHtml($SOURCE_MODULE)}">
			<input type="hidden" name="sourceRecord" value="{\App\Purifier::encodeHtml($SOURCE_RECORD)}">
		{/if}
		{foreach key=KEY item=VALUE from=$HIDDEN_FIELDS}
			<input type="hidden" name="{\App\Purifier::encodeHtml($KEY)}" value="{\App\Purifier::encodeHtml($VALUE)}">
		{/foreach}
		{assign var=ITERATION value=0}
		{foreach item=BLOCK from=$BLOCKS}
			{if isset($FIELDS_FORM[$BLOCK['id']])}
				{if $BLOCK['display_status'] eq 0}
					{assign var=IS_HIDDEN value=true}
				{else}
					{assign var=IS_HIDDEN value=false}
				{/if}
				<div class="c-card card my-3 blockContainer">
					<div class="c-card__header card-header p-2 {if $IS_HIDDEN}collapsed{/if}" data-toggle="collapse" data-target="#block_{$BLOCK['id']}" aria-expanded="true">
						<span class="fas fa-angle-right mr-2 c-card__icon-right {if !$IS_HIDDEN}d-none{/if}"></span>
						<span class="fas fa-angle-down mr-2 c-card__icon-down {if $IS_HIDDEN}d-none{/if}"></span>
						<h5>{if !empty($BLOCK['icon'])}<span class="{$BLOCK['icon']} mr-2"></span>{/if}{\App\Purifier::encodeHtml($BLOCK['name'])}</h5>
					</div>
					<div class="c-card__body card-body blockContent row m-0 {if $IS_HIDDEN}d-none{else}show{/if}" id="block_{$BLOCK['id']}">
						{foreach item=FIELD from=$FIELDS_FORM[$BLOCK['id']]}
							<div class="editFields {if $FIELD->getUIType() eq '300'}col-lg-12{else}col-sm-12 col-md-6{/if} row m-0 d-flex align-items-center {if !$FIELD->isEditable()}d-none{/if}">
								<div class="{if $FIELD->getUIType() eq '300'}col-lg-12 text-left{else}col-xl-3 col-lg-4 col-md-12{/if} fieldLabel paddingLeft5px font-weight-bold">
									<label class="muted mb-0 pt-0">
										{if $FIELD->isMandatory()}<span class="redColor">*</span>{/if}
										{\App\Purifier::encodeHtml($FIELD->getLabel())}
									</label>
								</div>
								<div class="fieldValue {if $FIELD->getUIType() eq '300'}col-lg-12{else}col-xl-9 col-lg-8 col-md-12{/if}  px-1">
									{include file=\App\Resources::templatePath($FIELD->getTemplatePath('Edit'), $MODULE_NAME) FIELD_MODEL=$FIELD}
								</div>
							</div>
						{/foreach}
					</div>
				</div>
				{assign var=ITERATION value=$ITERATION+1}
			{/if}
		{/foreach}
		<div class="c-form__action-panel d-flex justify-content-center">
			<button type="button" class="btn btn-success mr-3 js-form-submit" data-js="click">
				<span class="fas fa-check mr-2"></span>
				{\App\Language::translate('BTN_SAVE', $MODULE_NAME)}
			</button>
			<button type="button" class="btn btn-danger js-edit-back" data-js="click">
				<span class="fas fa-times mr-2"></span>
				{\App\Language::translate('BTN_CANCEL', $MODULE_NAME)}
			</button>
		</div>
	</form>
	<!-- /tpl-Base-Edit-Form -->
{/strip}
