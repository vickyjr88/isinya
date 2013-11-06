/*
 * Script Deals with creating all the charts found in reports
 * @author Joseph Bosire
 * @email kashboss@gmail.com
 */
/*
 * Draws Pie Charts on the respective reports
 * ==========================================
 */
$chartPlaceholder = $("#inv-stock-by-cat-pie-chart,#inv-stock-by-loc-pie-chart,#inv-stock-by-sup-pie-chart");
if(typeof(chartDataset)!=='undefined'){
	$chartPlaceholder.unbind();
$.plot($chartPlaceholder, chartDataset, {
	series: {
		pie: { show: true }
	},
    legend: {
        noColumns: 3,
        container: $("#chart-legend")
    }
});
}

/*
 * Deals with Inventory Stock Level Table Report
 * =============================================
 */
window.mainDataTable = initDataTable('#stock_levels', [-1]);

	$('table th input:checkbox').on('click', function() {
		var that = this;
		$(this).closest('table').find('tr > td:first-child input:checkbox').each(function() {
			this.checked = that.checked;
			$(this).closest('tr').toggleClass('selected');
		});
	});
