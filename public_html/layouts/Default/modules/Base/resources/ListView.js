/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.Base_ListView_Js = class {
	/**
	 * Register data table
	 */
	registerDataTable() {
		this.dataTable = app.registerDataTables(this.table, {
			order: [],
			processing: true,
			serverSide: true,
			searching: false,
			orderCellsTop: true,
			ajax: {
				url: 'index.php',
				type: 'POST',
				data: (data) => {
					$.extend(data, this.listForm.serializeFormData());
				},
				error: function (jqXHR, ajaxOptions, thrownError) {
					app.errorLog(jqXHR, thrownError);
					app.showNotify({
						text: thrownError,
						type: 'error',
						stack: window.stackPage
					});
				}
			}
		});
		this.listForm.find('input').on('change', () => {
			this.dataTable.ajax.reload();
		});
	}
	/**
	 * Register record events
	 */
	registerRecordEvents() {
		this.table.on('click', '.js-delete-record', (e) => {
			AppConnector.request({
				data: {},
				url: $(e.currentTarget).data('url')
			})
				.done((data) => {
					if (data.result) {
						this.dataTable.ajax.reload();
					}
				})
				.fail(function () {
					console.log(e, err);
				});
		});
		this.table.on('click', '.js-search-records', (e) => {
			this.dataTable.ajax.reload();
		});
		this.table.on('click', '.js-clear-search', (e) => {
			this.table.find('.js-filter-field').each(function (k, v) {
				this.value = '';
			});
			this.dataTable.ajax.reload();
		});
	}
	/**
	 * Register modal events.
	 */
	registerEvents() {
		this.listForm = $('.js-form-container');
		this.table = this.listForm.find('.js-list-view-table');
		this.registerDataTable();
		this.registerRecordEvents();
	}
};
