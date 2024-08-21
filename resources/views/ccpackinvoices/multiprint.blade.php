
<html>
<head>
    <title>table</title>
    <style type="text/css" media="all" >
.content {
    max-width: 100%;
    display: inline-block;
    width: 986px;
    margin: 0 auto;
    text-align: left;
}
.upper-case{
    display: inline-block;width: 100%;
}
.logo {
    display: inline-block;
    width: 38%;
    float: left;
}
.logo-right{
    display: inline-block;
    width: 62%;
    float: right;
    padding-left: 26px;
    box-sizing: border-box;    
}
.cash-down {
    display: inline-block;
    width: 100%;
    text-align: center;
    padding-top: 30px;
}
.right-div {
    display: inline-block;
}
body{
    text-align: center;
}
div{box-sizing: border-box;}

@page { size: landscape; }
</style>

</head>
<body>
    
<div class="content">
    <div class="upper-case">
        <div class="logo">
            <div style="float: left;">
                <img src="{{url('/images/ccpackLogo.png')}}" alt="">
            </div>
            <div style="float: left; margin-top: 13px;">
                <b>CCXpress S.A</b><br/>
                42, route de l'Aéroport, PAP, Haïti<br/>
                2250-1650(ext 501) / 2816-8181
            </div>  
        </div>
        <div class="logo-right">
            <div class="cash">
                <div style="float: right;"><b>CASH</b></div>
            </div>
            <div class="cash-down">
                <div class="right-div" style="float: left;"><b>Facture</b></div>
                <div class="right-div" style="float: left; padding-left: 15px;"><b>CCPACK</b></div>
                <div class="right-div"><b>{{$ccpackData->file_number}}</b></div>
                <div class="right-div" style="float: right; padding-right: 35px;"><b>8164018</b></div>
            </div>          
        </div>
    </div>
    <div class="middle-case" style="height:99px;width: 100%;">
        <div class="1st-row" style="    display: inline-block;  width: 100%;">
            <div class="1st-col" style="float: left;width: 3%;">
                <div style="float: left;height:100px;border:1px solid;padding-right: 17px;padding-top: 11px;background-color: lightgray;width: 100%;">
                    <div style="transform: rotate(270deg);transform-origin: right;margin-top: 40px;"> Shipper</div> 
                </div>
                <div style="float: left;height:100px;border:1px solid;padding-right: 17px;padding-top: 11px;background-color: lightgray;width: 100%; border-top: 0;">
                    <div style="transform: rotate(270deg);transform-origin: right;margin-top: 40px;"> Consignee</div> 
                </div>              
            </div>
            <div class="2nd-col" style="float: left;width: 20%;">
                <div style="padding-left: 10px;border:1px solid;height: 100px;vertical-align: middle; display: table;width: 100%; border-left: 0;">
                    <div style="display: table-cell; vertical-align: middle;">
                        <span><?php echo App\Ups::getConsigneeName($ccpackData->shipper_name)?></span>
                    </div>              
                </div>
                <div style="padding-left: 10px;border:1px solid;height: 100px;vertical-align: middle; display: table;width: 100%; border-left: 0; border-top: 0;">
                    <div style="display: table-cell; vertical-align: middle;">
                        <span><?php echo App\Ups::getConsigneeName($ccpackData->consignee)?></span>
                    </div>              
                </div>
            </div>
            <div class="3rd-col" style="float: left;width: 11%;">
                <div style="border-top:1px solid;background-color: lightgray; height: 33px;padding: 3px 0 0 3px;">
                    <span ><b>Date d'arrivée</b></span>
                </div>
                <div style="border-top:1px solid; height: 33px;padding: 3px 0 0 3px;">
                    <span>12/06/2018</span>
                </div>
                <div style="border-top:1px solid;background-color: lightgray; height: 33px;padding: 3px 0 0 3px;">
                    <span><b>Nbr. Pièces</b></span>
                </div>
                <div style="border-top:1px solid; height: 33px;padding: 3px 0 0 3px;">
                    <span >{{$ccpackData->no_of_pcs}}</span>
                </div>
                <div style="border-top:1px solid;background-color: lightgray; height: 33px;padding: 3px 0 0 3px;">
                    <span><b>Poids</b></span>
                </div>
                <div style="border:1px solid; height: 35px;padding: 3px 0 0 3px; border-left: 0;">
                    <span>{{$ccpackData->weight}}</span>
                </div>
            </div>
            <div class="4th-col" style="float: left;width: 22%;">
                <div style="height: 165px; padding: 5px;border-left: 1px solid;border-top: 1px solid;">
                    <div>
                        <span>N°. Tracking (CCX366924-366932)</span>
                    </div>
                    <div>
                        <span>{{$ccpackData->awb_number}}</span><br><br>
                    </div>
                    <div>
                        <span>EFFETS PERSONNELS</b></span>
                    </div>
                </div>
                <div style="height: 35px; padding: 0 5px;border-bottom: 1px solid;border-top: 1px solid;">
                    <span style="font-size: 13px;">Veuillez rédiger votre chèque au nom de CCXpress S.A</span>  
                </div>
            </div>
            <div class="4th-col" style="float: left;width: 22%;">
                <div style="height: 165px; padding: 5px;border-left: 1px solid;border-top: 1px solid;">
                    <div>
                        <span>Freight</span>
                        <span style="float: right;">{{$ccpackData->freight}} USD</span>
                    </div>
                    <div>
                        <span>Frais_US</span>
                        <span style="float: right;">{{$ccpackData->expences}} USD</span>
                    </div>                  
                </div>
                <div style="height: 35px; padding: 0 5px;border-bottom: 1px solid;border-top: 1px solid; border-left: 1px solid; padding: 5px 0 0 5px;">
                    <span style="font-size: 14px;float: left;text-align: right;width: 100%;padding: 5px 15px 0 5px;box-sizing: border-box;"><b>Total Dollar $ <?php echo $ccpackData->freight + $ccpackData->expences;?> USD</b></span> 
                </div>
            </div>
            <div class="4th-col" style="float: left;width: 22%;">
                <div style="height: 165px; padding: 5px;border-left: 1px solid;border-top: 1px solid;border-right: 1px solid;">
                    <div>
                        @foreach($items as $items)
                            <span>{{$items->fees_name_desc}}</span>
                            <span style="float: right;"> {{$items->unit_price}}HTG</span>
                        @endforeach
                    </div>      
                </div>
                <div style="height: 35px; padding: 0 5px;border-bottom: 1px solid;border-top: 1px solid;border-left: 1px solid;border-right: 1px solid; padding: 5px 0 0 5px;">
                    <span style="font-size: 14px;float: left;text-align: right;width: 100%;padding: 5px 15px 0 5px;box-sizing: border-box;"><b>Total Gde {{$invoiceData->total}} HTG</b></span>   
                </div>
            </div>  
        </div>
    </div>  
</div>  


</body>
</html>