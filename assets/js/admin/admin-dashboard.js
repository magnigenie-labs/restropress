var $ = jQuery;
$(document).ready(function($){
    $.ajax({
        url: rpress_vars.ajaxurl,
        type: 'POST',  
        dataType: 'json',
        data: {
            action: 'revenue_graph_filter',
            select_filter: 'monthly'
          },
        success: function(data) {
            setRevenueGraph(data,'monthly');
        },
        error: function(error) {
            
        }
      });
    $.ajax({
        url: rpress_vars.ajaxurl, 
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'customers_data_filter',
            selected_option: 'monthly',
        },
        success: function(data) {      
            $('#customer-data').text( data.customer_count );
            $('#new-customer').text( data.customer_count_last  );
            setcustomergraph(data.customer_count,data.customer_count_last);

        },
        error: function(error) {
            console.error('Error fetching data:', error);
        }
    });
    $.ajax({
        url: rpress_vars.ajaxurl,
        type: 'POST',  
        dataType: 'json',
        data: {
            action: 'order_graph_filter',
            select_filter: 'monthly'
          },
        success: function(data) {
            setOrderGraph(data,'monthly');
        },
        error: function(error) {
            
        }
     });
    $("#revenue-graph-selecter").change(function(){
         var selectedValue = $(this).val();
        $.ajax({
            url: rpress_vars.ajaxurl,
            type: 'POST',  
            dataType: 'json',
            data: {
                action: 'revenue_graph_filter',
                select_filter: selectedValue
              },
            success: function(data) {
                setRevenueGraph(data,selectedValue);
            },
            error: function(error) {
                
            }
          });   
    });
    $("#customersdata").change(function() {
        var selectedOption = $(this).val();
        $.ajax({
            url: rpress_vars.ajaxurl, 
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'customers_data_filter',
                selected_option: selectedOption,
            },
            success: function(data) {      
                $('#customer-data').text( data.customer_count );
                $('#new-customer').text( data.customer_count_last  );
                setcustomergraph(data.customer_count,data.customer_count_last);
    
            },
            error: function(error) {
                console.error('Error fetching data:', error);
            }
        });
     });
    $("#orderFrequency").change(function(){
        var selectedValue = $(this).val();
       $.ajax({
           url: rpress_vars.ajaxurl,
           type: 'POST',  
           dataType: 'json',
           data: {
               action: 'order_graph_filter',
               select_filter: selectedValue
             },
           success: function(data) {
               setOrderGraph(data,selectedValue);
           },
           error: function(error) {
               
           }
        });
    });
    crear_select();
});

function setRevenueGraph(data,selectedValue) {
    var sorteddata = Object.fromEntries(Object.entries(data).sort(([a], [b]) => Number(a) - Number(b)));
    var dataArray = Object.entries(sorteddata).map(([key, value]) => ({ y: value, label:key  })); 
    if ( selectedValue == 'monthly' || selectedValue =='weekly'){
        var xtitle = "Dates";
    } 
    
    else {
        var xtitle = "Months"; 
        dataArray = Object.entries(sorteddata).map(([key, value]) => ({ y: value, label:convertToMonthName(key)  })); 
    }  
    var chart = new CanvasJS.Chart("revenue-graph-container", {
        animationEnabled: true,
        theme: "light1 ",
        axisY: {
            title: "Revenue("+rpress_vars.currency_sign+")",
            gridDashType:"longDash"
        },
        data: [{        
            type: "column",  
            showInLegend: true, 
            legendMarkerColor: "white",
            legendText:xtitle,
            color:'#4E4BF5',
            dataPoints: dataArray
        }]
    });
    chart.render();
    jQuery(".canvasjs-chart-credit").css("display", "none");
     
}

function convertToMonthName(monthNumber) {
    const months = [
        "January", "February", "March", "April",
        "May", "June", "July", "August",
        "September", "October", "November", "December"
    ];
    return months[parseInt(monthNumber, 10) - 1];
}


function isMobileDevice() {
    return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
}

var li = [];

function crear_select() {
    var div_cont_select = $("[data-mate-select='active']");
    var select_ = '';
    div_cont_select.each(function(e) {
        $(this).attr('data-indx-select', e);
        $(this).attr('data-selec-open', 'false');
        var ul_cont = $("[data-indx-select='"+e+"'] > .list_multi-column-select > ul");
        select_ = $("[data-indx-select='"+e+"'] >select")[0];
        
        if (isMobileDevice()) { 
            $(select_).on('change', function() {
                _select_option(select_.selectedIndex, e);
            });
        }
        
        var select_optiones = select_.options;
        $("[data-indx-select='"+e+"']  > .selecionado_opcion ").attr('data-n-select', e);
        $("[data-indx-select='"+e+"']  > .arrow-icon_select ").attr('data-n-select', e);
        
        for (var i = 0; i < select_optiones.length; i++) {
            li[i] = document.createElement('li');
            if ( select_optiones[i].selected == true || select_.value == select_optiones[i].innerHTML ) {
                li[i].className = 'active';
                $("[data-indx-select='"+e+"']  > .selecionado_opcion ").html(select_optiones[i].innerHTML);
            };
            li[i].setAttribute('data-index', i);
            li[i].setAttribute('data-selec-index', e);
            $(li[i]).on('click', function() {  
                _select_option($(this).attr('data-index'), $(this).attr('data-selec-index')); 
            });
            li[i].innerHTML = select_optiones[i].innerHTML;
            ul_cont[0].appendChild(li[i]);
        }; 
    });
}

var cont_slc = 0;

function open_select( idx ) {
    var idx1 = $(idx).attr('data-n-select');
    var ul_cont_li = $("[data-indx-select='"+idx1+"'] .list-multi-select-option > li");
    var hg = 0;
    var slect_open = $("[data-indx-select='"+idx1+"']").attr('data-selec-open');
    var slect_element_open = $("[data-indx-select='"+idx1+"'] select")[0];
    
    if ( isMobileDevice() ) { 
        if (window.document.createEvent) {
            var evt = window.document.createEvent("MouseEvents");
            evt.initMouseEvent("mousedown", false, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
            slect_element_open.dispatchEvent(evt);
        } else if (slect_element_open.fireEvent) {
            slect_element_open.fireEvent("onmousedown");
        } else {
            slect_element_open.click();
        }
    } else {  
        ul_cont_li.each(function() {
            hg += $(this).outerHeight();
        }); 
        if (slect_open == 'false') {  
            $("[data-indx-select='"+idx1+"']").attr('data-selec-open', 'true');
            $("[data-indx-select='"+idx1+"'] > .list_multi-column-select > ul").css('height', 'auto');
            $("[data-indx-select='"+idx1+"'] > .arrow-icon_select").css('transform', 'rotate(180deg)');
        } else {
            $("[data-indx-select='"+idx1+"']").attr('data-selec-open', 'false');
            $("[data-indx-select='"+idx1+"'] > .arrow-icon_select").css('transform', 'rotate(0deg)');
            $("[data-indx-select='"+idx1+"'] > .list_multi-column-select > ul").css('height', '0px');
        }
    }
} 

function salir_select( indx ) {
    var select_ = $("[data-indx-select='"+indx+"'] > select")[0];
    $("[data-indx-select='"+indx+"'] > .list_multi-column-select > ul").css('height', '0px');
    $("[data-indx-select='"+indx+"'] > .arrow-icon_select").css('transform', 'rotate(0deg)');
    $("[data-indx-select='"+indx+"']").attr('data-selec-open', 'false');
}

function _select_option(indx, selc) {
     
    if (isMobileDevice()) { 
        selc = selc - 1;
    }
    var select_ = $("[data-indx-select='"+selc+"'] > select")[0];
    var li_s = $("[data-indx-select='"+selc+"'] .list-multi-select-option > li");
    var p_act = $("[data-indx-select='"+selc+"'] > .selecionado_opcion").html(li_s[indx].innerHTML);
    var select_optiones = $("[data-indx-select='"+selc+"'] > select > option");
     
    li_s.removeClass('active');
    $(li_s[indx]).addClass('active');
    select_optiones.eq(indx).prop('selected', true);
    select_.selectedIndex = indx;
    salir_select(selc);
    
      
    callAjax(indx,selc); 

}
function callAjax(indx,selc) {

    var selectedfilter ="";
    switch( indx ) {
        case "0":
            selectedfilter = "this_year"; 
            break;
        case "1":
            selectedfilter = "today";
            break;
        case "2":
            selectedfilter = "yesterday";
            break;
        case "3":
            selectedfilter = "last_week";
            break;        
        case "4":
            selectedfilter = "last_month";
            break;        
        case "5":
            selectedfilter = "last_year";
            break;        
        case "6":
            selectedfilter = "custom";
            break;
        default:
            selectedfilter = "empty_option";

    }
    
    if ( selectedfilter !== "" && selectedfilter !== "custom" ) {

        $('input[name="daterange"]').remove();
        var data = {
            action: 'selected_filter',
            date:   selectedfilter,
        };
        $.ajax( {
            url: rpress_vars.ajaxurl,
            type: 'POST', 
            data: data,
            success: function( response ) {
                $('body #total-order').text( response.order_count );
                $('body #total-customer').text(response.customer_count );
                $('body #total-refund').text( '$' + response.total_refund );
                $('body #total-sales').text( '$' + response.total_sales );
                $('body #total-order-percentage').text( response.order_percentage + "%" );
                    var percentageDiv = $("#total-order-percentage");
                    if ( response.order_percentage > 0 ) {
                        percentageDiv.text("+" + response.order_percentage + "%").removeClass( "rp-red" ).addClass( "rp-green" );
                        var svgGreen = '<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                            '<path d="M9.80976 0.664233V1.32844H10.3258H10.8444L9.01782 3.15499L7.19127 4.98155L5.8833 3.67358L4.57278 2.36306L2.28639 4.64945L0 6.93583L0.459832 7.39567L0.919665 7.8555L2.74622 6.02894L4.57278 4.20239L5.87053 5.50014C6.58327 6.21288 7.17849 6.79533 7.19127 6.79533C7.20404 6.79533 8.24377 5.76837 9.49554 4.51661L11.7768 2.23533V2.75392V3.26995H12.4282H13.0797V1.63499V3.09944e-05H11.4447H9.80976V0.664233Z" fill="#028614" />' +
                            '</svg>';
                            
                            percentageDiv.prepend(svgGreen);
                        
                    } else if ( response.order_percentage < 0 ) {

                        percentageDiv.text(response.order_percentage + "%").removeClass( "rp-green" ).addClass( "rp-red" );
                        var svgRed = '<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                            '<path d="M9.80976 7.19127V6.52706H10.3258H10.8444L9.01782 4.70051L7.19127 2.87395L5.8833 4.18192L4.57278 5.49244L2.28639 3.20605L0 0.919665L0.459832 0.459832L0.919665 0L2.74622 1.82656L4.57278 3.65311L5.87053 2.35536C6.58327 1.64262 7.17849 1.06017 7.19127 1.06017C7.20404 1.06017 8.24377 2.08713 9.49554 3.33889L11.7768 5.62017V5.10158V4.58555H12.4282H13.0797V6.22051V7.85547H11.4447H9.80976V7.19127Z" fill="#FF2F41" />' +
                            '</svg>';
                            
                        percentageDiv.prepend(svgRed);
                    }
                $('body #total-customer-percentage').text( response.customer_percentage + "%" );
                    var percentageDiv = $("#total-customer-percentage");
                    if ( response.customer_percentage > 0 ) {
                        percentageDiv.text("+" + response.customer_percentage + "%").removeClass( "rp-red" ).addClass( "rp-green" );
                        var svgGreen = '<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                            '<path d="M9.80976 0.664233V1.32844H10.3258H10.8444L9.01782 3.15499L7.19127 4.98155L5.8833 3.67358L4.57278 2.36306L2.28639 4.64945L0 6.93583L0.459832 7.39567L0.919665 7.8555L2.74622 6.02894L4.57278 4.20239L5.87053 5.50014C6.58327 6.21288 7.17849 6.79533 7.19127 6.79533C7.20404 6.79533 8.24377 5.76837 9.49554 4.51661L11.7768 2.23533V2.75392V3.26995H12.4282H13.0797V1.63499V3.09944e-05H11.4447H9.80976V0.664233Z" fill="#028614" />' +
                            '</svg>';
                            
                            percentageDiv.prepend(svgGreen);
                    } else if ( response.customer_percentage < 0 ) {
                        percentageDiv.text(response.customer_percentage + "%").removeClass( "rp-green" ).addClass( "rp-red" );
                        var svgRed = '<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                            '<path d="M9.80976 7.19127V6.52706H10.3258H10.8444L9.01782 4.70051L7.19127 2.87395L5.8833 4.18192L4.57278 5.49244L2.28639 3.20605L0 0.919665L0.459832 0.459832L0.919665 0L2.74622 1.82656L4.57278 3.65311L5.87053 2.35536C6.58327 1.64262 7.17849 1.06017 7.19127 1.06017C7.20404 1.06017 8.24377 2.08713 9.49554 3.33889L11.7768 5.62017V5.10158V4.58555H12.4282H13.0797V6.22051V7.85547H11.4447H9.80976V7.19127Z" fill="#FF2F41" />' +
                            '</svg>';
                            
                        percentageDiv.prepend(svgRed); 
                    }
                $('body #total-refund-percentage').text( response.refund_percentage + "%" );
                    var percentageDiv = $("#total-refund-percentage");
                    if ( response.refund_percentage > 0 ) {
                        percentageDiv.text("+" + response.refund_percentage + "%").removeClass( "rp-red" ).addClass( "rp-green" );
                        var svgGreen = '<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                            '<path d="M9.80976 0.664233V1.32844H10.3258H10.8444L9.01782 3.15499L7.19127 4.98155L5.8833 3.67358L4.57278 2.36306L2.28639 4.64945L0 6.93583L0.459832 7.39567L0.919665 7.8555L2.74622 6.02894L4.57278 4.20239L5.87053 5.50014C6.58327 6.21288 7.17849 6.79533 7.19127 6.79533C7.20404 6.79533 8.24377 5.76837 9.49554 4.51661L11.7768 2.23533V2.75392V3.26995H12.4282H13.0797V1.63499V3.09944e-05H11.4447H9.80976V0.664233Z" fill="#028614" />' +
                            '</svg>';
                            
                        percentageDiv.prepend(svgGreen);
                    } else if ( response.refund_percentage < 0 ) {
                        percentageDiv.text( response.refund_percentage + "%").removeClass( "rp-green" ).addClass( "rp-red" );
                        var svgRed = '<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                            '<path d="M9.80976 7.19127V6.52706H10.3258H10.8444L9.01782 4.70051L7.19127 2.87395L5.8833 4.18192L4.57278 5.49244L2.28639 3.20605L0 0.919665L0.459832 0.459832L0.919665 0L2.74622 1.82656L4.57278 3.65311L5.87053 2.35536C6.58327 1.64262 7.17849 1.06017 7.19127 1.06017C7.20404 1.06017 8.24377 2.08713 9.49554 3.33889L11.7768 5.62017V5.10158V4.58555H12.4282H13.0797V6.22051V7.85547H11.4447H9.80976V7.19127Z" fill="#FF2F41" />' +
                            '</svg>';
                            
                        percentageDiv.prepend(svgRed); 
                    }
                $('body #total-sales-percentage').text( response.sales_percentage + "%" );
                    var percentageDiv = $( "#total-sales-percentage" );
                    if ( response.sales_percentage > 0 ) {
                        percentageDiv.text("+" + response.sales_percentage + "%").removeClass( "rp-red" ).addClass( "rp-green" );
                        var svgGreen = '<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                            '<path d="M9.80976 0.664233V1.32844H10.3258H10.8444L9.01782 3.15499L7.19127 4.98155L5.8833 3.67358L4.57278 2.36306L2.28639 4.64945L0 6.93583L0.459832 7.39567L0.919665 7.8555L2.74622 6.02894L4.57278 4.20239L5.87053 5.50014C6.58327 6.21288 7.17849 6.79533 7.19127 6.79533C7.20404 6.79533 8.24377 5.76837 9.49554 4.51661L11.7768 2.23533V2.75392V3.26995H12.4282H13.0797V1.63499V3.09944e-05H11.4447H9.80976V0.664233Z" fill="#028614" />' +
                            '</svg>';
                            
                        percentageDiv.prepend(svgGreen);
                    } else if ( response.sales_percentage < 0 ) {
                        percentageDiv.text( response.sales_percentage + "%").removeClass( "rp-green" ).addClass( "rp-red" );
                        var svgRed = '<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                            '<path d="M9.80976 7.19127V6.52706H10.3258H10.8444L9.01782 4.70051L7.19127 2.87395L5.8833 4.18192L4.57278 5.49244L2.28639 3.20605L0 0.919665L0.459832 0.459832L0.919665 0L2.74622 1.82656L4.57278 3.65311L5.87053 2.35536C6.58327 1.64262 7.17849 1.06017 7.19127 1.06017C7.20404 1.06017 8.24377 2.08713 9.49554 3.33889L11.7768 5.62017V5.10158V4.58555H12.4282H13.0797V6.22051V7.85547H11.4447H9.80976V7.19127Z" fill="#FF2F41" />' +
                            '</svg>';
                            
                        percentageDiv.prepend(svgRed); 
                    }
            },
            error: function(xhr, status, error) {
                // Handle errors
                console.error(error);
            }
        });
    
    } else if ( selectedfilter !== "" && selectedfilter == "custom" ) {

        if ( $('input[name="daterange"]').length === 0 ) {

            $('<input type="text" name="daterange" class="custom"/>').insertBefore('#rpress-dashboard-admin');
        
            var startDate, endDate;
        
            $('input[name="daterange"]').daterangepicker({
                opens: 'left'
            }, function(start, end, label) {
                startDate = start.format('YYYY-MM-DD');
                endDate = end.format('YYYY-MM-DD');
                
                var data = {
                    action: 'selected_filter',
                    date:   selectedfilter,
                    startDate: startDate,
                    endDate: endDate
                };
                $.ajax( {
                    url: rpress_vars.ajaxurl,
                    type: 'POST', 
                    data: data,
                    success: function( response ) {
                        $('body #total-order').text( response.order_count );
                        $('body #total-customer').text(response.customer_count );
                        $('body #total-refund').text( '$' + response.total_refund );
                        $('body #total-sales').text( '$' + response.total_sales );
                        $('body #total-order-percentage').text( response.order_percentage + "%" );
                            var percentageDiv = $("#total-order-percentage");
                            if ( response.order_percentage > 0 ) {
                                percentageDiv.text("+" + response.order_percentage + "%").removeClass( "rp-red" ).addClass( "rp-green" );
                                var svgGreen = '<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                                    '<path d="M9.80976 0.664233V1.32844H10.3258H10.8444L9.01782 3.15499L7.19127 4.98155L5.8833 3.67358L4.57278 2.36306L2.28639 4.64945L0 6.93583L0.459832 7.39567L0.919665 7.8555L2.74622 6.02894L4.57278 4.20239L5.87053 5.50014C6.58327 6.21288 7.17849 6.79533 7.19127 6.79533C7.20404 6.79533 8.24377 5.76837 9.49554 4.51661L11.7768 2.23533V2.75392V3.26995H12.4282H13.0797V1.63499V3.09944e-05H11.4447H9.80976V0.664233Z" fill="#028614" />' +
                                    '</svg>';
                                    
                                    percentageDiv.prepend(svgGreen);
                                
                            } else if ( response.order_percentage < 0 ) {
        
                                percentageDiv.text(response.order_percentage + "%").removeClass( "rp-green" ).addClass( "rp-red" );
                                var svgRed = '<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                                    '<path d="M9.80976 7.19127V6.52706H10.3258H10.8444L9.01782 4.70051L7.19127 2.87395L5.8833 4.18192L4.57278 5.49244L2.28639 3.20605L0 0.919665L0.459832 0.459832L0.919665 0L2.74622 1.82656L4.57278 3.65311L5.87053 2.35536C6.58327 1.64262 7.17849 1.06017 7.19127 1.06017C7.20404 1.06017 8.24377 2.08713 9.49554 3.33889L11.7768 5.62017V5.10158V4.58555H12.4282H13.0797V6.22051V7.85547H11.4447H9.80976V7.19127Z" fill="#FF2F41" />' +
                                    '</svg>';
                                    
                                percentageDiv.prepend(svgRed);
                            }
                        $('body #total-customer-percentage').text( response.customer_percentage + "%" );
                            var percentageDiv = $("#total-customer-percentage");
                            if ( response.customer_percentage > 0 ) {
                                percentageDiv.text("+" + response.customer_percentage + "%").removeClass( "rp-red" ).addClass( "rp-green" );
                                var svgGreen = '<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                                    '<path d="M9.80976 0.664233V1.32844H10.3258H10.8444L9.01782 3.15499L7.19127 4.98155L5.8833 3.67358L4.57278 2.36306L2.28639 4.64945L0 6.93583L0.459832 7.39567L0.919665 7.8555L2.74622 6.02894L4.57278 4.20239L5.87053 5.50014C6.58327 6.21288 7.17849 6.79533 7.19127 6.79533C7.20404 6.79533 8.24377 5.76837 9.49554 4.51661L11.7768 2.23533V2.75392V3.26995H12.4282H13.0797V1.63499V3.09944e-05H11.4447H9.80976V0.664233Z" fill="#028614" />' +
                                    '</svg>';
                                    
                                    percentageDiv.prepend(svgGreen);
                            } else if ( response.customer_percentage < 0 ) {
                                percentageDiv.text(response.customer_percentage + "%").removeClass( "rp-green" ).addClass( "rp-red" );
                                var svgRed = '<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                                    '<path d="M9.80976 7.19127V6.52706H10.3258H10.8444L9.01782 4.70051L7.19127 2.87395L5.8833 4.18192L4.57278 5.49244L2.28639 3.20605L0 0.919665L0.459832 0.459832L0.919665 0L2.74622 1.82656L4.57278 3.65311L5.87053 2.35536C6.58327 1.64262 7.17849 1.06017 7.19127 1.06017C7.20404 1.06017 8.24377 2.08713 9.49554 3.33889L11.7768 5.62017V5.10158V4.58555H12.4282H13.0797V6.22051V7.85547H11.4447H9.80976V7.19127Z" fill="#FF2F41" />' +
                                    '</svg>';
                                    
                                percentageDiv.prepend(svgRed); 
                            }
                        $('body #total-refund-percentage').text( response.refund_percentage + "%" );
                            var percentageDiv = $("#total-refund-percentage");
                            if ( response.refund_percentage > 0 ) {
                                percentageDiv.text("+" + response.refund_percentage + "%").removeClass( "rp-red" ).addClass( "rp-green" );
                                var svgGreen = '<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                                    '<path d="M9.80976 0.664233V1.32844H10.3258H10.8444L9.01782 3.15499L7.19127 4.98155L5.8833 3.67358L4.57278 2.36306L2.28639 4.64945L0 6.93583L0.459832 7.39567L0.919665 7.8555L2.74622 6.02894L4.57278 4.20239L5.87053 5.50014C6.58327 6.21288 7.17849 6.79533 7.19127 6.79533C7.20404 6.79533 8.24377 5.76837 9.49554 4.51661L11.7768 2.23533V2.75392V3.26995H12.4282H13.0797V1.63499V3.09944e-05H11.4447H9.80976V0.664233Z" fill="#028614" />' +
                                    '</svg>';
                                    
                                percentageDiv.prepend(svgGreen);
                            } else if ( response.refund_percentage < 0 ) {
                                percentageDiv.text( response.refund_percentage + "%").removeClass( "rp-green" ).addClass( "rp-red" );
                                var svgRed = '<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                                    '<path d="M9.80976 7.19127V6.52706H10.3258H10.8444L9.01782 4.70051L7.19127 2.87395L5.8833 4.18192L4.57278 5.49244L2.28639 3.20605L0 0.919665L0.459832 0.459832L0.919665 0L2.74622 1.82656L4.57278 3.65311L5.87053 2.35536C6.58327 1.64262 7.17849 1.06017 7.19127 1.06017C7.20404 1.06017 8.24377 2.08713 9.49554 3.33889L11.7768 5.62017V5.10158V4.58555H12.4282H13.0797V6.22051V7.85547H11.4447H9.80976V7.19127Z" fill="#FF2F41" />' +
                                    '</svg>';
                                    
                                percentageDiv.prepend(svgRed); 
                            }
                        $('body #total-sales-percentage').text( response.sales_percentage + "%" );
                            var percentageDiv = $( "#total-sales-percentage" );
                            if ( response.sales_percentage > 0 ) {
                                percentageDiv.text("+" + response.sales_percentage + "%").removeClass( "rp-red" ).addClass( "rp-green" );
                                var svgGreen = '<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                                    '<path d="M9.80976 0.664233V1.32844H10.3258H10.8444L9.01782 3.15499L7.19127 4.98155L5.8833 3.67358L4.57278 2.36306L2.28639 4.64945L0 6.93583L0.459832 7.39567L0.919665 7.8555L2.74622 6.02894L4.57278 4.20239L5.87053 5.50014C6.58327 6.21288 7.17849 6.79533 7.19127 6.79533C7.20404 6.79533 8.24377 5.76837 9.49554 4.51661L11.7768 2.23533V2.75392V3.26995H12.4282H13.0797V1.63499V3.09944e-05H11.4447H9.80976V0.664233Z" fill="#028614" />' +
                                    '</svg>';
                                    
                                percentageDiv.prepend(svgGreen);
                            } else if ( response.sales_percentage < 0 ) {
                                percentageDiv.text( response.sales_percentage + "%").removeClass( "rp-green" ).addClass( "rp-red" );
                                var svgRed = '<svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                                    '<path d="M9.80976 7.19127V6.52706H10.3258H10.8444L9.01782 4.70051L7.19127 2.87395L5.8833 4.18192L4.57278 5.49244L2.28639 3.20605L0 0.919665L0.459832 0.459832L0.919665 0L2.74622 1.82656L4.57278 3.65311L5.87053 2.35536C6.58327 1.64262 7.17849 1.06017 7.19127 1.06017C7.20404 1.06017 8.24377 2.08713 9.49554 3.33889L11.7768 5.62017V5.10158V4.58555H12.4282H13.0797V6.22051V7.85547H11.4447H9.80976V7.19127Z" fill="#FF2F41" />' +
                                    '</svg>';
                                    
                                percentageDiv.prepend(svgRed); 
                            }
                    },
                    error: function(xhr, status, error) {
                        
                        console.error(error);
                    }
                });
            }); 
        }
    }

     
}

var RPRESS_Export = {

    init: function () {
      this.submit();
      this.dismiss_message();
    },

    submit: function () {

      var self = this;

      $(document.body)
        .on('submit', '.rpress-export-form', function (e) {
          e.preventDefault();

          var submitButton = $(this)
            .find('input[type="submit"]');

          if (!submitButton.hasClass('button-disabled')) {

            var data = $(this)
              .serialize();

            submitButton.addClass('button-disabled');
            $(this)
              .find('.notice-wrap')
              .remove();
            $(this)
              .append('<div class="notice-wrap"><span class="spinner is-active"></span><div class="rpress-progress"><div></div></div></div>');

            // start the process
            self.process_step(1, data, self);

          }

        });
    },

    process_step: function (step, data, self) {
      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
          form: data,
          action: 'rpress_do_ajax_export',
          step: step,
        },
        dataType: "json",
        success: function (response) {
          if ('done' == response.step || response.error || response.success) {

            // We need to get the actual in progress form, not all forms on the page
            var export_form = $('.rpress-export-form')
              .find('.rpress-progress')
              .parent()
              .parent();
            var notice_wrap = export_form.find('.notice-wrap');

            export_form.find('.button-disabled')
              .removeClass('button-disabled');

            if (response.error) {

              var error_message = response.message;
              $('body #rpress-export-payments').text('');
              $('#error-messages').text(error_message);
              notice_wrap.html('<div class="updated error"><p>' + error_message + '</p></div>');

            } else if (response.success) {

              var success_message = response.message;
              notice_wrap.html('<div id="rpress-batch-success" class="updated notice is-dismissible"><p>' + success_message + '<span class="notice-dismiss"></span></p></div>');

            } 
             else {

              notice_wrap.remove();
              window.location = response.url;

            }

          } else {
            $('.rpress-progress div')
              .animate({
                width: response.percentage + '%',
              }, 50, function () {
                // Animation complete.
              });
            self.process_step(parseInt(response.step), data, self);
          }

        }
      })
        .fail(function (response) {
          if (window.console && window.console.log) {
            console.log(response);
          }
        });

    },

    dismiss_message: function () {
      $(document.body)
        .on('click', '#rpress-batch-success .notice-dismiss', function () {
          $('#rpress-batch-success')
            .parent()
            .slideUp('fast');
        });
    }

  };
RPRESS_Export.init();


function setOrderGraph(data,selectedValue) {
    if (selectedValue === 'monthly') {
        xtitle = "Dates";
        var sorteddata = Object.fromEntries(Object.entries(data).sort(([a], [b]) => Number(a) - Number(b)));
        var dataArray = Object.entries(sorteddata).map(([key, value]) => ({ y: value, label: key }));
    } else if (selectedValue === 'weekly') {
        xtitle = "Days";
        dataArray = Object.entries(data).map(([date, count]) => ({ y: count, label: convertToDayName(date) }));
    }else if (selectedValue === 'yearly') {
        xtitle = "Months";
        var sorteddata = Object.fromEntries(Object.entries(data).sort(([a], [b]) => Number(a) - Number(b)));
        dataArray = Object.entries(sorteddata).map(([key, value]) => ({ y: value, label: convertToMonthName(key) }));
    } 
    var color = "#4E4BF5"; 
    var chart = new CanvasJS.Chart("chartContainer", {
        animationEnabled: true,
        theme: "light2 ",
        title:{
            text: ""
        },
        axisY: {
            title: "Order"
        },
        
        data: [{        
            type: "column",  
            showInLegend: true, 
            legendMarkerColor: "white",
            legendText:xtitle,
            color: color,
            dataPoints: dataArray
        }]
    });
    chart.render();     
    jQuery(".canvasjs-chart-credit").css("display", "none");
}
function convertToDayName(dateString) {
    const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    const year = new Date().getFullYear(); 
    const month = new Date().getMonth() + 1; 
    const day = parseInt(dateString); 
    const date = new Date(year, month - 1, day); 
    return days[date.getDay()];
}
function convertToMonthName(monthNumber) {
    const months = [
        "January", "February", "March", "April",
        "May", "June", "July", "August",
        "September", "October", "November", "December"
    ];

    return months[parseInt(monthNumber, 10) - 1];
}



function setcustomergraph(customerCount,lastcustomerCount) {
    var chart = new CanvasJS.Chart("rp-customer-information-graph", {
        animationEnabled: true,
        
        data: [{
            type: "pie",
            startAngle: 240,
            yValueFormatString: "##0.00\"%\"",
            indexLabel: "{label} {y}",
          indexLabelPlacement: "inside",
            dataPoints: [
                {y: customerCount},
                {y: lastcustomerCount},
                
            ]
        }]
    });
    chart.render();
    jQuery(".canvasjs-chart-credit").css("display", "none");   
}




 