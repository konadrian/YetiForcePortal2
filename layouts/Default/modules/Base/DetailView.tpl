{strip}
	<div class="widget_header">
		<div class="row">
			<div class="col-xs-12">
				<div class="pull-left">
					{include file=FN::templatePath("BreadCrumbs.tpl",$MODULE_NAME)}
				</div>
			</div>
		</div>
	</div>
	<hr>
	{foreach item=FIELDS key=BLOCK from=$DETAIL}
		<div class="panel panel-default col-xs-12 paddingLRZero">
			<div class="panel-heading">{$BLOCK}</div>
				{foreach item=FIELD key=NAME from=$FIELDS}
					<div class='col-sm-12 col-md-6 paddingLRZero'>
						<div class='fieldName col-sm-6 col-md-6'>{$NAME}</div>
						<div class='fieldValue col-sm-6 col-md-6'>{$FIELD}</div>
					</div>
				{/foreach}
		</div>
	{/foreach}
{/strip}

