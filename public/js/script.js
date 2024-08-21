setTimeout(function () {$(".alert-success").fadeOut(1500);}, 2000);        
setTimeout(function () {$(".alert-info").fadeOut(1500);}, 2000);        
setTimeout(function () {$(".alert-danger").fadeOut(1500);}, 2000);


$(document).ready(function(){
    jQuery.extend(jQuery.fn.dataTableExt.oSort, {
        "date-uk-pre": function(a) {
            if (a == null || a == "") {
                return 0;
            }
            var ukDatea = a.split('-');
            return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
        },

        "date-uk-asc": function(a, b) {
            return ((a < b) ? -1 : ((a > b) ? 1 : 0));
        },

        "date-uk-desc": function(a, b) {
            return ((a < b) ? 1 : ((a > b) ? -1 : 0));
        }
    });
$('#loading').show();
$("#loading").fadeOut(1000);
initTooltip('*');
});
function initTooltip(target) {
    $(target).tooltip({
        track: true,
        content: function () {
            return $(this).attr("data-title");
        },
        show: {
            delay: 1500
        },
        open: function (event, ui) {
            setTimeout(function () {
                $(ui.tooltip).fadeTo(1000, 0);
            }, 5000);
        }
    });
};

// Below Js used for bootstrap modal popup
$(document).on('click', '#addNewItems', function(){
 
 var mod = $(this).data('module'); 
 $('.modal-title-block').text('Add '+mod);
    if(mod == 'HAWB Import File')
         $('#modalAddNewItemsImport').modal('show').find('#modalContentAddNewItemsImport').load($(this).attr('value'));
    else if(mod == 'HAWB Export File')
         $('#modalAddNewItemsExport').modal('show').find('#modalContentAddNewItemsExport').load($(this).attr('value'));
    else if (mod == 'CCPack HAWB Import File')
        $('#modalAddNewItemsImport').modal('show').find('#modalContentAddNewItemsImport').load($(this).attr('value'));
    else
         $('#modalAddNewItems').modal('show').find('#modalContentAddNewItems').load($(this).attr('value'));
});
$(document).on('click', '#upload-file-btn', function(){
    $('.modal-title-block').text("Upload Files");
    $('#modalUploadNewFiles').modal('show').find('#modalContentUploadNewFiles').load($(this).attr('value'));
});
$(document).on('click', '#addNewCheck', function () {
    $('#modalAddCheck').modal('show').find('#modalContentAddCheck').load($(this).attr('value'));
});

$(document).on('click', '#editNewCheck', function () {
    $('#modalEditCheck').modal('show').find('#modalContentEditCheck').load($(this).attr('value'));
});


 $(document).delegate(".viewdetail", "click", function(){
    var mod = $(this).data('module');
    $('.modal-title-block').text(mod);
    $('#modalViewUpsDetail').modal('show').find('#modalContentViewUpsDetail').load($(this).attr('value'));
   
});
$(document).on('click', '#btnViewDetail', function(){
 $('#modalViewDetail').modal('show').find('#modalContentViewDetail').load($(this).attr('value'));
});
$(document).on('click', '#btnViewActivities', function(){
 $('#modalViewUserActivities').modal('show').find('#modalContentViewUserActivities').load($(this).attr('value'));
});
$(document).on('click', '#btnCreateExpense', function(){
 $('#modalCreateExpense').modal('show').find('#modalContentCreateExpense').load($(this).attr('value'));
});
$(document).on('click', '#btnAddWarehouseInFile', function(){
 var mod = $(this).data('module'); 
 $('.modal-title-block').text('Add '+mod);
 $('#modalAddCashCreditWarehouseInFile').modal('show').find('#modalContentAddCashCreditWarehouseInFile').load($(this).attr('value'));
});
$(document).on('click', '#btnAddCashCreditInFile', function(){
 var mod = $(this).data('module'); 
 $('.modal-title-block').text('Add '+mod);
 $('#modalAddCashCreditWarehouseInFile').modal('show').find('#modalContentAddCashCreditWarehouseInFile').load($(this).attr('value'));
});
$(document).on('click', '#btnAddRackLocation', function(e){
    e.preventDefault();
 $('#modalAddRackLocation').modal('show').find('#modalContentAddRackLocation').load($(this).attr('value'));
});
$(document).on('click', '#btnAddVerificationNote', function(e){
    e.preventDefault();
 $('#modalAddVerificationNote').modal('show').find('#modalContentVerificationNote').load($(this).attr('value'));
});
$(document).on('click', '#btnViewVerificationNote', function(e){
    e.preventDefault();
 $('#modalViewVerificationNote').modal('show').find('#modalContentViewVerificationNote').load($(this).attr('value'));
});
$(document).on('dblclick', '.edit-row', function (e) {
    e.preventDefault();
    if (typeof ($(this).attr('data-editlink')) == "undefined" || typeof ($(this).attr('id')) == "undefined"){
        return false;
    }else{
        var updateURL = $(this).attr('data-editlink');
        location.href = updateURL;
    }
});
$(document).on('dblclick', '.edit-row-WH', function (e) {
    e.preventDefault();
    if (typeof ($(this).attr('data-editlink')) == "undefined" || typeof ($(this).attr('id')) == "undefined") {
        return false;
    } else {
        var updateURL = $(this).attr('data-editlink');
        //location.href = updateURL;
        window.open(
            updateURL,
            '_blank' // <- This is what makes it open in a new window.
        );
    }
});

//close Modal by Esc key.
$(document).on('keyup',function(e){
    if(e.keyCode == 27){
            $('.modal').modal('hide');
        }
});


$(document).on('click', '.delete-record-in-popup', function(e) {
            e.preventDefault(); // does not go through with the link.
            var thiz = $(this);
            var table = $('#example1').DataTable();
            thiz.parent().parent().parent().addClass('selected');
             if (confirm("Are you sure you want to delete ?") == true) {
                 $('#loading').show();
                 $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                });
                 $.ajax({
                    type: 'POST',
                    url: thiz.attr('href'),
                    success: function (response) {
                        table.row('.selected').remove().draw( false );
                        thiz.parent().parent().parent().remove();  //remove respective 'tr' from table
                        $('.flash-success-ajax-popup').show().text('Record has been deleted successfully.');
                        setTimeout(function () {$(".flash-success-ajax-popup").fadeOut(1500);}, 2000);        
                        $('#loading').hide();
                    },
                    error: function (MLHttpRequest, textStatus, errorThrown) {
                        $('#loading').hide();
                         console.log(errorThrown)
                        alert("Oops.something went wrong while processing your request");
                    }
                });
             }else
             {
                thiz.parent().parent().parent().removeClass('selected');
             }
        });
$(document).on('click', '.delete-record', function(e) {

            e.preventDefault(); // does not go through with the link.
            var thiz = $(this);
            console.log(thiz);
            var table = $('#'+$(this).parents('td').parents('tr').parents('table').attr('id')).DataTable();
            
            thiz.parent().parent().parent().addClass('selected');
             if (confirm("Are you sure you want to delete ?") == true) {
                 $('#loading').show();
                 $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                });
                 $.ajax({
                    type: 'POST',
                    url: thiz.attr('href'),
                    success: function (response) {
                        if(response == 'N'){
                            alert("This account has non-zero balance so you can't delete this account.");
                            $('#loading').hide();
                            thiz.parent().parent().parent().removeClass('selected');
                        } else {
                            console.log(response);
                            table.row('.selected').remove().draw( false );
                            thiz.parent().parent().parent().remove();
                            Lobibox.notify('info', {
                                size: 'mini',
                                delay: 2000,
                                rounded: true,
                                delayIndicator: false,
                                msg: 'Record has been deleted successfully.'
                            });
                            $('#loading').hide();

                        }
                         //remove respective 'tr' from table
                        //$('.flash-success-ajax').show().text('Record has been deleted successfully.');
                        //setTimeout(function () {$(".flash-success-ajax").fadeOut(1500);}, 2000);        
                        

                         
                    },
                    error: function (MLHttpRequest, textStatus, errorThrown) {
                        $('#loading').hide();

                         console.log(errorThrown)
                        alert("Oops.something went wrong while processing your request");
                    }


                });
                 
                
             }else
             {
                thiz.parent().parent().parent().removeClass('selected');
             }
        });

$(document).on('click', '.delete-record-expense', function(e) {
            var pageURL = $(location).attr("href");
            e.preventDefault(); // does not go through with the link.
            var thiz = $(this);
            var table = $('#example').DataTable();
            thiz.parent().parent().parent().addClass('selected');
             if (confirm("Are you sure you want to delete ?") == true) {
                 $('#loading').show();
                 $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                });
                 $.ajax({
                    type: 'POST',
                    url: thiz.attr('href'),
                    success: function (response) {

                        thiz.parent().parent().parent().remove();  //remove respective 'tr' from table
                        $('.flash-success-ajax').show().text('Record has been deleted successfully.');
                        setTimeout(function () {$(".flash-success-ajax").fadeOut(1500);}, 2000);        
                        $('#loading').hide();
                        window.location.href = pageURL;
                    },
                    error: function (MLHttpRequest, textStatus, errorThrown) {
                        $('#loading').hide();
                         console.log(errorThrown)
                        alert("Oops.something went wrong while processing your request");
                    }
                });
             }else
             {
                thiz.parent().parent().parent().removeClass('selected');
             }
        });

function changeValue(data){
    $.each(data, function( index, value ) {
        if(typeof value!='object'){
            if($('select#'+index).length){
                $('select#'+index+' option[value="'+value+'"]').attr("selected","selected").trigger("change");
                // $('select#'+index).trigger('change');
            }else if($('input#'+index).length){
                $('input#'+index).val(value);
            }
        }
    });
}


function _init(){"use strict";$.AdminLTE.layout={activate:function(){var a=this;a.fix(),a.fixSidebar(),$("body, html, .wrapper").css("height","auto"),$(window,".wrapper").resize(function(){a.fix(),a.fixSidebar()})},fix:function(){$(".layout-boxed > .wrapper").css("overflow","hidden");var a=$(".main-footer").outerHeight()||0,b=$(".main-header").outerHeight()+a,c=$(window).height(),d=$(".sidebar").height()||0;if($("body").hasClass("fixed"))$(".content-wrapper, .right-side").css("min-height",c-a);else{var e;c>=d?($(".content-wrapper, .right-side").css("min-height",c-b),e=c-b):($(".content-wrapper, .right-side").css("min-height",d),e=d);var f=$($.AdminLTE.options.controlSidebarOptions.selector);void 0!==f&&f.height()>e&&$(".content-wrapper, .right-side").css("min-height",f.height())}},fixSidebar:function(){if(!$("body").hasClass("fixed"))return void(void 0!==$.fn.slimScroll&&$(".sidebar").slimScroll({destroy:!0}).height("auto"));void 0===$.fn.slimScroll&&window.console&&window.console.error("Error: the fixed layout requires the slimscroll plugin!"),$.AdminLTE.options.sidebarSlimScroll&&void 0!==$.fn.slimScroll&&($(".sidebar").slimScroll({destroy:!0}).height("auto"),$(".sidebar").slimScroll({height:$(window).height()-$(".main-header").height()+"px",color:"rgba(0,0,0,0.2)",size:"3px"}))}},$.AdminLTE.pushMenu={activate:function(a){var b=$.AdminLTE.options.screenSizes;$(document).on("click",a,function(a){a.preventDefault(),$(window).width()>b.sm-1?$("body").hasClass("sidebar-collapse")?$("body").removeClass("sidebar-collapse").trigger("expanded.pushMenu"):$("body").addClass("sidebar-collapse").trigger("collapsed.pushMenu"):$("body").hasClass("sidebar-open")?$("body").removeClass("sidebar-open").removeClass("sidebar-collapse").trigger("collapsed.pushMenu"):$("body").addClass("sidebar-open").trigger("expanded.pushMenu")}),$(".content-wrapper").click(function(){$(window).width()<=b.sm-1&&$("body").hasClass("sidebar-open")&&$("body").removeClass("sidebar-open")}),($.AdminLTE.options.sidebarExpandOnHover||$("body").hasClass("fixed")&&$("body").hasClass("sidebar-mini"))&&this.expandOnHover()},expandOnHover:function(){var a=this,b=$.AdminLTE.options.screenSizes.sm-1;$(".main-sidebar").hover(function(){$("body").hasClass("sidebar-mini")&&$("body").hasClass("sidebar-collapse")&&$(window).width()>b&&a.expand()},function(){$("body").hasClass("sidebar-mini")&&$("body").hasClass("sidebar-expanded-on-hover")&&$(window).width()>b&&a.collapse()})},expand:function(){$("body").removeClass("sidebar-collapse").addClass("sidebar-expanded-on-hover")},collapse:function(){$("body").hasClass("sidebar-expanded-on-hover")&&$("body").removeClass("sidebar-expanded-on-hover").addClass("sidebar-collapse")}},$.AdminLTE.tree=function(a){var b=this,c=$.AdminLTE.options.animationSpeed;$(document).off("click",a+" li a").on("click",a+" li a",function(a){var d=$(this),e=d.next();if(e.is(".treeview-menu")&&e.is(":visible")&&!$("body").hasClass("sidebar-collapse"))e.slideUp(c,function(){e.removeClass("menu-open")}),e.parent("li").removeClass("active");else if(e.is(".treeview-menu")&&!e.is(":visible")){var f=d.parents("ul").first(),g=f.find("ul:visible").slideUp(c);g.removeClass("menu-open");var h=d.parent("li");e.slideDown(c,function(){e.addClass("menu-open"),f.find("li.active").removeClass("active"),h.addClass("active"),b.layout.fix()})}e.is(".treeview-menu")&&a.preventDefault()})},$.AdminLTE.controlSidebar={activate:function(){var a=this,b=$.AdminLTE.options.controlSidebarOptions,c=$(b.selector);$(b.toggleBtnSelector).on("click",function(d){d.preventDefault(),c.hasClass("control-sidebar-open")||$("body").hasClass("control-sidebar-open")?a.close(c,b.slide):a.open(c,b.slide)});var d=$(".control-sidebar-bg");a._fix(d),$("body").hasClass("fixed")?a._fixForFixed(c):$(".content-wrapper, .right-side").height()<c.height()&&a._fixForContent(c)},open:function(a,b){b?a.addClass("control-sidebar-open"):$("body").addClass("control-sidebar-open")},close:function(a,b){b?a.removeClass("control-sidebar-open"):$("body").removeClass("control-sidebar-open")},_fix:function(a){var b=this;if($("body").hasClass("layout-boxed")){if(a.css("position","absolute"),a.height($(".wrapper").height()),b.hasBindedResize)return;$(window).resize(function(){b._fix(a)}),b.hasBindedResize=!0}else a.css({position:"fixed",height:"auto"})},_fixForFixed:function(a){a.css({position:"fixed","max-height":"100%",overflow:"auto","padding-bottom":"50px"})},_fixForContent:function(a){$(".content-wrapper, .right-side").css("min-height",a.height())}},$.AdminLTE.boxWidget={selectors:$.AdminLTE.options.boxWidgetOptions.boxWidgetSelectors,icons:$.AdminLTE.options.boxWidgetOptions.boxWidgetIcons,animationSpeed:$.AdminLTE.options.animationSpeed,activate:function(a){var b=this;a||(a=document),$(a).on("click",b.selectors.collapse,function(a){a.preventDefault(),b.collapse($(this))}),$(a).on("click",b.selectors.remove,function(a){a.preventDefault(),b.remove($(this))})},collapse:function(a){var b=this,c=a.parents(".box").first(),d=c.find("> .box-body, > .box-footer, > form  >.box-body, > form > .box-footer");c.hasClass("collapsed-box")?(a.children(":first").removeClass(b.icons.open).addClass(b.icons.collapse),d.slideDown(b.animationSpeed,function(){c.removeClass("collapsed-box")})):(a.children(":first").removeClass(b.icons.collapse).addClass(b.icons.open),d.slideUp(b.animationSpeed,function(){c.addClass("collapsed-box")}))},remove:function(a){a.parents(".box").first().slideUp(this.animationSpeed)}}}if("undefined"==typeof jQuery)throw new Error("AdminLTE requires jQuery");$.AdminLTE={},$.AdminLTE.options={navbarMenuSlimscroll:!0,navbarMenuSlimscrollWidth:"3px",navbarMenuHeight:"200px",animationSpeed:500,sidebarToggleSelector:"[data-toggle='offcanvas']",sidebarPushMenu:!0,sidebarSlimScroll:!0,sidebarExpandOnHover:!1,enableBoxRefresh:!0,enableBSToppltip:!0,BSTooltipSelector:"[data-toggle='tooltip']",enableFastclick:!1,enableControlTreeView:!0,enableControlSidebar:!0,controlSidebarOptions:{toggleBtnSelector:"[data-toggle='control-sidebar']",selector:".control-sidebar",slide:!0},enableBoxWidget:!0,boxWidgetOptions:{boxWidgetIcons:{collapse:"fa-minus",open:"fa-plus",remove:"fa-times"},boxWidgetSelectors:{remove:'[data-widget="remove"]',collapse:'[data-widget="collapse"]'}},directChat:{enable:!0,contactToggleSelector:'[data-widget="chat-pane-toggle"]'},colors:{lightBlue:"#3c8dbc",red:"#f56954",green:"#00a65a",aqua:"#00c0ef",yellow:"#f39c12",blue:"#0073b7",navy:"#001F3F",teal:"#39CCCC",olive:"#3D9970",lime:"#01FF70",orange:"#FF851B",fuchsia:"#F012BE",purple:"#8E24AA",maroon:"#D81B60",black:"#222222",gray:"#d2d6de"},screenSizes:{xs:480,sm:768,md:992,lg:1200}},$(function(){"use strict";$("body").removeClass("hold-transition"),"undefined"!=typeof AdminLTEOptions&&$.extend(!0,$.AdminLTE.options,AdminLTEOptions);var a=$.AdminLTE.options;_init(),$.AdminLTE.layout.activate(),a.enableControlTreeView&&$.AdminLTE.tree(".sidebar"),a.enableControlSidebar&&$.AdminLTE.controlSidebar.activate(),a.navbarMenuSlimscroll&&void 0!==$.fn.slimscroll&&$(".navbar .menu").slimscroll({height:a.navbarMenuHeight,alwaysVisible:!1,size:a.navbarMenuSlimscrollWidth}).css("width","100%"),a.sidebarPushMenu&&$.AdminLTE.pushMenu.activate(a.sidebarToggleSelector),a.enableBSToppltip&&$("body").tooltip({selector:a.BSTooltipSelector,container:"body"}),a.enableBoxWidget&&$.AdminLTE.boxWidget.activate(),a.enableFastclick&&"undefined"!=typeof FastClick&&FastClick.attach(document.body),a.directChat.enable&&$(document).on("click",a.directChat.contactToggleSelector,function(){$(this).parents(".direct-chat").first().toggleClass("direct-chat-contacts-open")}),$('.btn-group[data-toggle="btn-toggle"]').each(function(){var a=$(this);$(this).find(".btn").on("click",function(b){a.find(".btn.active").removeClass("active"),$(this).addClass("active"),b.preventDefault()})})}),function(a){"use strict";a.fn.boxRefresh=function(b){function c(a){a.append(f),e.onLoadStart.call(a)}function d(a){a.find(f).remove(),e.onLoadDone.call(a)}var e=a.extend({trigger:".refresh-btn",source:"",onLoadStart:function(a){return a},onLoadDone:function(a){return a}},b),f=a('<div class="overlay"><div class="fa fa-refresh fa-spin"></div></div>');return this.each(function(){if(""===e.source)return void(window.console&&window.console.log("Please specify a source first - boxRefresh()"));var b=a(this);b.find(e.trigger).first().on("click",function(a){a.preventDefault(),c(b),b.find(".box-body").load(e.source,function(){d(b)})})})}}(jQuery),function(a){"use strict";a.fn.activateBox=function(){a.AdminLTE.boxWidget.activate(this)},a.fn.toggleBox=function(){var b=a(a.AdminLTE.boxWidget.selectors.collapse,this);a.AdminLTE.boxWidget.collapse(b)},a.fn.removeBox=function(){var b=a(a.AdminLTE.boxWidget.selectors.remove,this);a.AdminLTE.boxWidget.remove(b)}}(jQuery),function(a){"use strict";a.fn.todolist=function(b){var c=a.extend({onCheck:function(a){return a},onUncheck:function(a){return a}},b);return this.each(function(){void 0!==a.fn.iCheck?(a("input",this).on("ifChecked",function(){var b=a(this).parents("li").first();b.toggleClass("done"),c.onCheck.call(b)}),a("input",this).on("ifUnchecked",function(){var b=a(this).parents("li").first();b.toggleClass("done"),c.onUncheck.call(b)})):a("input",this).on("change",function(){var b=a(this).parents("li").first();b.toggleClass("done"),a("input",b).is(":checked")?c.onCheck.call(b):c.onUncheck.call(b)})})}}(jQuery);