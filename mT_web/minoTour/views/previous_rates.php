<?php
// load the functions
require_once("includes/functions.php");

?>
<!DOCTYPE html>
<html>

<?php include "includes/head.php";?>
<body>

    <div id="wrapper">

        <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom: 0">

			<?php include 'navbar-header.php' ?>
            <!-- /.navbar-top-links -->
			<?php include 'navbar-top-links.php'; ?>
            <!-- /.navbar-static-side -->
        </nav>

        <div id="page-wrapper">
						<?php include 'includes/run_check.php';?>
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Previous Data Summary - run: <?php echo cleanname($_SESSION['focusrun']); ?></h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
			<ul class="nav nav-pills">
			  <li><a href="previous_summary.php">Read Summaries</a></li>
              <?php if ($_SESSION['focusbasesum'] > 0){?>
              <li><a href="previous_basecalling.php">Basecaller Summary</a></li>
              <?php }; ?>
			  <li><a href="previous_histogram.php">Read Histograms</a></li>
			  <li class="active"><a href="previous_rates.php">Sequencing Rates</a></li>
			  <li><a href="previous_pores.php">Pore Activity</a></li>
  			  <li><a href="previous_quality.php">Read Quality</a></li>
  			   <?php if ($_SESSION['focusreference'] != "NOREFERENCE") {?>
  			  <li><a href="previous_coverage.php">Coverage Detail</a></li>
  			  <?php }; ?>
  			  <li><a href="previous_bases.php">Base Coverage</a></li>
  			  <!--<li class="active"><a href="previous_development.php">W.I.M.M (Dev)</a></li>-->
			</ul>

			<div class="panel panel-default">
			  <div class="panel-heading">
			    <h3 class="panel-title"><!-- Button trigger modal -->
<button class="btn btn-info" data-toggle="modal" data-target="#modal2">
 <i class="fa fa-info-circle"></i> Sequencing Rate Information</h4>
</button>

<!-- Modal -->
<div class="modal fade" id="modal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"> Sequencing Rate Information
      </div>
      <div class="modal-body">
        Rate of Basecalling<br>
		This plot show the number of reads generated in one minute intervals over the course of the sequencing run.<br><br>
		Average Read Length Over Time<br>
		This plot shows the average length of read generated each minute over the course of the sequncing run.<br><br>
		Average Time To Process Reads Over Time<br>
		This plot shows how long a singel read takes to pass through the pore over the course of the sequencing run (1 minute bins).<br><br>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div></h3>
			  </div>
              <div class="panel-body">
					<div id="cumulativeyield" style="width:100%; height:400px;"><i class="fa fa-cog fa-spin fa-3x"></i> Calculating Cumulative Reads</div>
			  		<div id="sequencingrate" style="height:400px;"><i class="fa fa-cog fa-spin fa-3x"></i> Calculating Sequencing Rates.</div>
                    <div id="ratio2dtemplate" style="height:400px;"><i class="fa fa-cog fa-spin fa-3x"></i> Calculating Ratio 2D to Template.</div>
                    <?php if ($_SESSION['focusBASE'] > 0) {?>
                    <div id="ratiopassfail" style="height:400px;"><i class="fa fa-cog fa-spin fa-3x"></i> Calculating Pass Fail Reads.</div>
					<div id="readrate" style="width:100%; height:400px;"><i class="fa fa-cog fa-spin fa-3x"></i> Calculating Read Rate</div>
					<div id="averagelength" style="width:100%; height:400px;"><i class="fa fa-cog fa-spin fa-3x"></i> Calculating Read Length Over Time</div>
					<div id="averagetime" style="width:100%; height:400px;"><i class="fa fa-cog fa-spin fa-3x"></i> Calculating Time To Complete Reads</div>
                    <?php }; ?>
			  </div>
			</div>

                <!-- /.col-lg-12 -->
            </div>
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->


    <!-- Core Scripts - Include with every page -->
    <script src="js/jquery-1.10.2.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/plugins/metisMenu/jquery.metisMenu.js"></script>

    <!-- Page-Level Plugin Scripts - Dashboard -->
			    <script type="text/javascript" src="js/pnotify.custom.min.js"></script>
			    <script type="text/javascript">
				PNotify.prototype.options.styling = "fontawesome";
				</script>
    <script src="js/plugins/morris/raphael-2.1.0.min.js"></script>
    <script src="js/plugins/morris/morris.js"></script>

	<!-- Highcharts Addition -->
	<script src="http://code.highcharts.com/highcharts.js"></script>
	<script type="text/javascript" src="js/themes/grid-light.js"></script>
	<script src="http://code.highcharts.com/modules/heatmap.js"></script>
	<script src="http://code.highcharts.com/modules/exporting.js"></script>
<script src="https://raw.githubusercontent.com/highcharts/export-csv/master/export-csv.js"></script>

	<script>
		$(document).ready(function() {
		    var options = {
                chart: {
		            renderTo: 'cumulativeyield',
					zoomType: 'x',
		            type: 'spline',
		        },
		        title: {
		          text: 'Cumulative Reads'
		        },
		        resetZoomButton: {
                position: {
                    // align: 'right', // by default
                    // verticalAlign: 'top', // by default
                    x: -10,
                    y: 10
                },
                relativeTo: 'chart'
            },
		        plotOptions: {
		        	spline: {
					                animation: false,
									marker: {
							            enabled: false
							        }

				},



        },
				xAxis: {
					type: 'datetime',
			            dateTimeLabelFormats: { // don't display the dummy year
               				month: '%e. %b',
           				    year: '%b'
				            },
				            title: {
				                text: 'Time (S)'
				            }
				        },
						yAxis: [{
				                labels: {
            				        align: 'right',
            	    			    x: -3
            	   				},

            	    			title: {
            	        			text: 'Cumulative Reads'
				                },
				                height: '100%',
				                lineWidth: 1,
				                min: 0
				            }],
								credits: {
								    enabled: false
								  },
		        legend: {
		        	title: {
                text: 'Read Type <span style="font-size: 9px; color: #666; font-weight: normal">(Click to hide)</span>',
                style: {
                    fontStyle: 'italic'
                }
            },

		            layout: 'horizontal',
		            align: 'center',
		            //verticalAlign: 'middle',
		            borderWidth: 0
		        },
		        series: []
		    };

		    $.getJSON('jsonencode/cumulativeyield.php?prev=1&callback=?', function(data) {
				//alert("success");
		        options.series = data; // <- just assign the data to the series property.



		        //options.series = JSON2;
				var chart = new Highcharts.Chart(options);
				});
		});

			//]]>

			</script>

            <script>
            		$(document).ready(function() {
            		    var options = {
            		        chart: {
            		            renderTo: 'sequencingrate',
            					zoomType: 'x',
            		            type: 'spline',
            		        },
            		        title: {
            		          text: 'Sequencing Rate'
            		        },
            		        resetZoomButton: {
                            position: {
                                // align: 'right', // by default
                                // verticalAlign: 'top', // by default
                                x: -10,
                                y: 10
                            },
                            relativeTo: 'chart'
                        },
            		        plotOptions: {
            		        	spline: {
            					                animation: false,
            									marker: {
            							            enabled: false
            							        }

            				},



                    },
            				xAxis: {
            					type: 'datetime',
            			            dateTimeLabelFormats: { // don't display the dummy year
                           				month: '%e. %b',
                       				    year: '%b'
            				            },
            				            title: {
            				                text: 'Time/Date'
            				            }
            				        },
            						yAxis:
            							{

            				                labels: {
                        				        align: 'right',
                        	    			    x: -3
                        	   				},

                        	    			title: {
                        	        			text: 'Bases/Second'
            				                },
            				                height: '100%',
            				                lineWidth: 1,
            				                min: 0
            				            },
            								credits: {
            								    enabled: false
            								  },
            		        legend: {
            		        	title: {
                            text: 'Read Type <span style="font-size: 9px; color: #666; font-weight: normal">(Click to hide)</span>',
                            style: {
                                fontStyle: 'italic'
                            }
                        },

            		            layout: 'horizontal',
            		            align: 'center',
            		            //verticalAlign: 'middle',
            		            borderWidth: 0
            		        },
            		        series: []
            		    };

               			$.getJSON('jsonencode/sequencingrate.php?prev=1&callback=?', function(data) {

                        options.series = data; // <- just assign the data to the series property.

                        //options.series = JSON2;
                        var chart = new Highcharts.Chart(options);

            });


            			});

            				//]]>

            </script>

			<script>

			$(document).ready(function() {
			    var options = {
			        chart: {
			            renderTo: 'ratiopassfail',
						//zoomType: 'x'
			            type: 'spline'
			        },
					plotOptions: {
					            spline: {
					                animation: false,
                                    marker:{
                                        enabled: false,
                                    }

					            }
					        },
			        title: {
			          text: '2d, Complement and Template Pass/Fail Counts in 15 minute windows'
			        },
					xAxis: {
						type: 'datetime',
			            dateTimeLabelFormats: { // don't display the dummy year
               				month: '%e. %b',
           				    year: '%b'
				            },
					            title: {
					                text: 'Time/Date'
					            }
					        },
							yAxis: {
							            title: {
							                text: 'Reads'
							            },
							            min: 0
							        },
									credits: {
									    enabled: false
									  },
			        legend: {
			            layout: 'horizontal',
                                        align: 'center',
                                        verticalAlign: 'bottom',
			            borderWidth: 0
			        },
			        series: []
			    };

			    $.getJSON('jsonencode/ratiopassfail.php?prev=1&callback=?', function(data) {
				//alert("success");
		        options.series = data; // <- just assign the data to the series property.



		        //options.series = JSON2;
				var chart = new Highcharts.Chart(options);
				});
		});



				//]]>

</script>




<script>

			$(document).ready(function() {
			    var options = {
			        chart: {
			            renderTo: 'ratio2dtemplate',
						//zoomType: 'x'
			            type: 'spline'
			        },
					plotOptions: {
					            spline: {
					                animation: false,
                                    marker: {
                                        enabled: false,
                                    }

					            }
					        },
			        title: {
			          text: '2d, Complement and Template reads in 15 minute windows'
			        },
					xAxis: {
						type: 'datetime',
			            dateTimeLabelFormats: { // don't display the dummy year
               				month: '%e. %b',
           				    year: '%b'
				            },
					            title: {
					                text: 'Time/Date'
					            }
					        },
							yAxis: {
							            title: {
							                text: 'Reads'
							            },
							            min: 0
							        },
									credits: {
									    enabled: false
									  },
			        legend: {
			            layout: 'horizontal',
                                        align: 'center',
                                        verticalAlign: 'bottom',
			            borderWidth: 0
			        },
			        series: []
			    };
			    $.getJSON('jsonencode/ratio2dtemplate.php?prev=1&callback=?', function(data) {
				//alert("success");
		        options.series = data; // <- just assign the data to the series property.



		        //options.series = JSON2;
				var chart = new Highcharts.Chart(options);
				});
		});

				//]]>

</script>





	<script>

$(document).ready(function() {
    var options = {
			        chart: {
			            renderTo: 'readrate',
						zoomType: 'x'
			            //type: 'line'
			        },
					plotOptions: {
					            line: {
					                animation: false

					            }
					        },
			        title: {
			          text: 'Rate Of BaseCalling'
			        },
					xAxis: {
					type: 'datetime',
			            dateTimeLabelFormats: { // don't display the dummy year
               				month: '%e. %b',
           				    year: '%b',
				            },
				            title: {
				                text: 'Time/Date'
				            }
				        },
							yAxis: {
							            title: {
							                text: 'Reads/Minute'
							            },
							            min: 0
							        },
									credits: {
									    enabled: false
									  },
			        legend: {
		        	title: {
                text: 'Read Type <span style="font-size: 9px; color: #666; font-weight: normal">(Click to hide)</span>',
                style: {
                    fontStyle: 'italic'
                }
            },
			            layout: 'horizontal',
                                        align: 'center',
                                        verticalAlign: 'bottom',
			            borderWidth: 0
			        },
			        series: []
			    };
    $.getJSON('jsonencode/reads_over_time2.php?prev=1&callback=?', function(data) {
		//alert("success");
        options.series = data; // <- just assign the data to the series property.



        //options.series = JSON2;
		var chart = new Highcharts.Chart(options);
		});
});

	//]]>

	</script>

	<script>

	$(document).ready(function() {
	    var options = {
	        chart: {
	            renderTo: 'averagelength',
				zoomType: 'x'
	            //type: 'line'
	        },
	        title: {
	          text: 'Average Read Length Over Time'
	        },
			xAxis: {
				type: 'datetime',
			            dateTimeLabelFormats: { // don't display the dummy year
               				month: '%e. %b',
           				    year: '%b',
				            },
			            title: {
			                text: 'Time/Date'
			            }
			        },
					yAxis: {
					            title: {
					                text: 'Average Read Length'
					            },
					            min: 0
					        },

							credits: {
							    enabled: false
							  },
	        legend: {
	            title: {
                text: 'Read Type <span style="font-size: 9px; color: #666; font-weight: normal">(Click to hide)</span>',
                style: {
                    fontStyle: 'italic'
                }
            },
			            layout: 'horizontal',
                                        align: 'center',
                                        verticalAlign: 'bottom',
			            borderWidth: 0
	        },
	        series: []
	    };

	    $.getJSON('jsonencode/average_length_over_time.php?prev=1&callback=?', function(data) {
			//alert("success");
	        options.series = data; // <- just assign the data to the series property.



	        //options.series = JSON2;
			var chart = new Highcharts.Chart(options);
			});
	});

		//]]>

		</script>

		<script>

	$(document).ready(function() {
	    var options = {
	        chart: {
	            renderTo: 'averagetime',
				zoomType: 'x'
	            //type: 'line'
	        },
	        title: {
	          text: 'Average Time to process Reads Over Time'
	        },
			xAxis: {
				type: 'datetime',
			            dateTimeLabelFormats: { // don't display the dummy year
               				month: '%e. %b',
           				    year: '%b',
				            },
			            title: {
			                text: 'Time/Date'
			            }
			        },
					yAxis: {
					            title: {
					                text: 'Average Time To Process Read (s)'
					            },
					            min: 0
					        },
							credits: {
							    enabled: false
							  },
	        legend: {
	            title: {
                text: 'Read Type <span style="font-size: 9px; color: #666; font-weight: normal">(Click to hide)</span>',
                style: {
                    fontStyle: 'italic'
                }
            },
			            layout: 'horizontal',
                                        align: 'center',
                                        verticalAlign: 'bottom',
			            borderWidth: 0

	        },
	        series: []
	    };

	    $.getJSON('jsonencode/average_time_over_time2.php?prev=1&callback=?', function(data) {
			//alert("success");
	        options.series = data; // <- just assign the data to the series property.



	        //options.series = JSON2;
			var chart = new Highcharts.Chart(options);
			});
	});

		//]]>

		</script>

    <!-- SB Admin Scripts - Include with every page -->
    <script src="js/sb-admin.js"></script>

    <!-- Page-Level Demo Scripts - Dashboard - Use for reference -->
    <script src="js/demo/dashboard-demo.js"></script>

     <script>
        $( "#infodiv" ).load( "alertcheck.php" ).fadeIn("slow");
        var auto_refresh = setInterval(function ()
            {
            $( "#infodiv" ).load( "alertcheck.php" ).fadeIn("slow");
            //eval(document.getElementById("infodiv").innerHTML);
            }, 10000); // refresh every 5000 milliseconds
    </script>

<?php include "includes/reporting.php";?>
</body>

</html>
