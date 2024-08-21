    @extends('layouts.custom')
    @section('title')
    Cost Details
    @stop
    @section('sidebar')
    <aside class="main-sidebar">
        <ul class="sidemenu nav navbar-nav side-nav">
            <?php 
        $checkPermissionCreateCargoImport = App\User::checkPermission(['import_cargo'],'',auth()->user()->id); 
        $checkPermissionUpdateCargoImport = App\User::checkPermission(['update_import'],'',auth()->user()->id); 
        $checkPermissionDeleteCargoImport = App\User::checkPermission(['delete_import'],'',auth()->user()->id);
        $checkPermissionImportIndexCargo = App\User::checkPermission(['import_cargo_index'],'',auth()->user()->id);     
        $checkPermissionAddImportExpenseCargo = App\User::checkPermission(['add_cargo_import_expense'],'',auth()->user()->id);


        $checkPermissionCreateCargoExport = App\User::checkPermission(['export_cargo'],'',auth()->user()->id); 
        $checkPermissionUpdateCargoExport = App\User::checkPermission(['update_export'],'',auth()->user()->id); 
        $checkPermissionDeleteCargoExport = App\User::checkPermission(['delete_export'],'',auth()->user()->id);
        $checkPermissionExportIndexCargo = App\User::checkPermission(['export_cargo_index'],'',auth()->user()->id);  
        $checkPermissionAddExportExpenseCargo = App\User::checkPermission(['add_cargo_export_expense'],'',auth()->user()->id);


        $checkPermissionCreateCargoLocale = App\User::checkPermission(['locale_cargo'],'',auth()->user()->id); 
        $checkPermissionUpdateCargoLocale = App\User::checkPermission(['update_locale'],'',auth()->user()->id); 
        $checkPermissionDeleteCargoLocale = App\User::checkPermission(['delete_locale'],'',auth()->user()->id);
        $checkPermissionLocaleIndexCargo = App\User::checkPermission(['locale_cargo_index'],'',auth()->user()->id);  
        $checkPermissionAddLocaleExpenseCargo = App\User::checkPermission(['add_cargo_locale_expense'],'',auth()->user()->id);
        
        //echo View::make('layouts.cargomenu',['id'=>0,'flag'=>'listing'])->render(); 
        ?>
        <?php if($checkPermissionImportIndexCargo) { ?>
                <li class="widemenu">
                    <a href="{{ route('viewcargo',[$rid,$id]) }}">Basic</a>
                </li>
        <?php } 
            if($checkPermissionCreateCargoImport) { ?>
                <li class="widemenu">
                    <a href="{{ route('cargoexpensedetail',[$rid,$id]) }}">Expense</a>
                </li>
        <?php } ?>
                <li class="widemenu">
                    <a href="{{ route('invoicedetail',[$rid,$id]) }}">Invoice</a>
                </li>
                <li class="widemenu active">
                    <a href="{{ route('costdetail',[$rid,$id]) }}">Cost</a>
                </li>
                <li class="widemenu">
                    <a href="{{ route('reportdetail',[$rid,$id]) }}">Report</a>
                </li>

                        

        </ul>
    </aside>
    @stop
    @section('content')
    <section class="content-header">
        <h1>Cost Detail</h1>
    </section>

    <section class="content editupscontainer">
        @if(Session::has('flash_message'))
            <div class="alert alert-success flash-success">
                {{ Session::get('flash_message') }}
            </div>
        @endif
        @if(Session::has('flash_message_error'))
            <div class="alert alert-danger flash-danger">
                {{ Session::get('flash_message_error') }}
            </div>
        @endif
        
        <div class="alert alert-success flash-success flash-success-ajax" style="display: none"></div>
        <div class="box box-success">
        <div class="box-body" style="float: left">
        Coming Soon...
            </div>
            </div>

    
                    
            
    </section>
    @endsection
    @section('page_level_js')
    <script type="text/javascript">
    $(document).ready(function() {
         

    })
    </script>
    @stop

