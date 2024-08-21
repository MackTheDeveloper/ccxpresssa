<section class="content" style="font-family: sans-serif;">
    <div class="box box-success" style="width: 100%;margin: 0px auto;">
        <div class="box-body cargo-forms" style="color: #000">

            <h3 style="background: #ccc;padding:5px;font-weight:normal;width:100%">
                <div style="float: left;width: 50%">Invoice : {{$invoice['bill_no']}}</div>
                <div style="float: right;width: 50%;text-align: right;"><b>Currency : <?php $data = app('App\Currency')::getData($invoice['currency']);
                                                                                        echo !empty($data->code) ? strtoupper($data->code) : "-"; ?></b></div>
            </h3>


            <div class="col-md-12" style="margin-top: 20px">
                <div class="sec1" style="float: left;width: 100%">
                    <div class="d1 col-md-2" style="width: 20%;float: left;">
                        {{ Form::image('images/invoice_logo.png', 'alt text', array('class' => 'css-class')) }}
                    </div>
                    <div class="d2 col-md-8" style="width: 60%;float: left;text-align: center;">
                        <h3 style="text-align: center;font-weight: bold;font-size: 25px;font-style: italic;margin-top: 0px;">Chatelain Cargo Services S.A</h3>

                        <div style="width: 100%;text-align: center;font-size: 10px">Aeroport International de Port-au-Prince, P.O.Box 1056 Port-au-Prince, Haiti</div>
                        <div style="width: 100%;float: left;text-align: center;font-size: 10px">Tel: (509) 250-1652 a 250-1656, Fax: (509) 250-3898(P-A-P)</div>
                        <div style="width: 100%;text-align: center;font-size: 10px">Fax: (1-305) 436-3793(U.S.A)</div>
                        <div style="width: 100%;float: left;text-align: center;font-size: 10px">Email: pvc@chatelaincargo.com</div>

                    </div>
                    <div class="d3 col-md-2" style="font-weight: bold;text-align: right;margin-top: 8%;width: 20%;float: left;">INVOICE</div>
                </div>
                <div class="sec2" style="float: left;width: 100%;margin-top: 20px">
                    <div style="width: 70%;float: left;border:1px #ccc solid;">
                        <div class="col-md-12" style="width: 100%;float: left;margin-bottom: 5px">
                            <div style="float: left;width: 23%;">
                                <div><b>Billing party:</b></div>
                            </div>
                            <div style="float: left;width: 50%;text-align: left;">
                                <div><?php $dataUser = app('App\Clients')->getClientData($invoice['bill_to']);
                                        echo !empty($dataUser->company_name) ? strtoupper($dataUser->company_name) : "-"; ?></div>
                            </div>
                        </div>
                        <div style="width: 100%">
                            <div style="width: 50%;float: left;">
                                <div style="float: left;width: 20%"><b style="font-size: 14px">Email: </b></div>
                                <div style="float: left;width: 80%">
                                    <div><?php echo $invoice['email']; ?></div>
                                </div>
                            </div>
                            <div style="width: 50%;float: left;">
                                <div style="float: left;width: 9%;padding-left: 10px;"><b style="font-size: 14px">Tel: </b></div>
                                <div style="float: left;width: 50%">
                                    <div><?php echo $invoice['telephone']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="float: right;width: 30%;float: left;">
                        <table style="border: 1px solid #ccc;width: 100%;font-family: sans-serif;color: #000">
                            <tr style="border-collapse: collapse;height: 39px;text-align: center;">
                                <td style="border-right: 1px solid #ccc;border-bottom: 1px solid #ccc;">Date</td>
                                <td style="border-bottom: 1px solid #ccc;">No facture</td>
                            </tr>
                            <tr>
                                <td style="border-right: 1px solid #ccc">
                                    <div><?php echo date('d/m/Y', strtotime($invoice['date'])); ?></div>
                                </td>
                                <td style="pointer-events: none;opacity: 0.5">
                                    <div><?php echo $invoice['bill_no']; ?></div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="sec3" style="float: left;width: 100%;margin-top: 10px">
                    <div style="width: 48%;float: left;border: 1px solid #ccc;">
                        <div style="font-size: 12px;padding: 5px;width: 100%;text-align: center;border-bottom:1px solid #ccc;margin-bottom: 0px;"><b>Expediteur / Shipper</b></div>
                        <div style="height: 100px;padding: 10px"><?php echo $invoice['shipper']; ?></div>
                    </div>
                    <div style="width: 48%;float: left;margin-left: 30px;border: 1px solid #ccc;">
                        <div style="font-size: 12px;padding: 5px;width: 100%;text-align: center;border-bottom:1px solid #ccc;margin-bottom: 0px;"><b>Consignataire / Consignee</b></div>
                        <div style="height: 100px;padding: 10px"><?php echo nl2br($invoice['consignee_address']); ?></div>
                    </div>
                </div>
                <div class="sec4" style="float: left;width: 100%;margin-top: 10px">
                    <div class="col-md-12">
                        <table CELLSPACING="0" CELLPADDING="0" style="border: 1px solid #ccc;width: 100%;font-family: sans-serif;color: #000">
                            <tr style="border-bottom: 1px solid #ccc;height: 39px;text-align: center;font-weight: bold;">
                                <td style="padding:5px;border-right: 1px solid #ccc;border-bottom: 1px solid #ccc;text-align: center;font-weight: bold;font-size: 12px">No. Dossier/ File No</td>
                                <td style="padding:5px;border-right: 1px solid #ccc;border-bottom: 1px solid #ccc;text-align: center;font-weight: bold;font-size: 12px">AWB / BL No.</td>
                                <td style="padding:5px;border-right: 1px solid #ccc;border-bottom: 1px solid #ccc;text-align: center;font-weight: bold;font-size: 12px">Transporteur / Carrier</td>
                                <td style="padding:5px;border-right: 1px solid #ccc;border-bottom: 1px solid #ccc;text-align: center;font-weight: bold;font-size: 12px">Import / Export</td>
                                <td style="padding:5px;border-bottom: 1px solid #ccc;text-align: center;font-weight: bold;font-size: 12px">Poids / Weight</td>
                            </tr>
                            <tr>
                                <td style="padding:5px;text-align: center;border-right: 1px solid #ccc">
                                    <div><?php echo $invoice['file_no']; ?></div>
                                </td>
                                <td style="padding:5px;text-align: center;border-right: 1px solid #ccc">
                                    <div><?php echo $invoice['awb_no']; ?></div>
                                <td style="padding:5px;text-align: center;border-right: 1px solid #ccc">
                                    <div><?php echo $invoice['carrier']; ?></div>
                                </td>
                                <td style="padding:5px;text-align: center;border-right: 1px solid #ccc">
                                    <div><?php echo $invoice['type_flag']; ?></div>
                                </td>
                                <td style="padding:5px;text-align: center;">
                                    <div><?php echo $invoice['weight']; ?></div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="sec5" style="float: left;width: 100%;margin-top: 10px">
                    <div class="col-md-12">
                        <table CELLSPACING="0" CELLPADDING="0" style="border: 1px solid #ccc;width: 100%;font-family: sans-serif;color: #000">
                            <thead>
                                <tr style="border-bottom: 1px solid #ccc;height: 39px;text-align: center;font-weight: bold;">
                                    <td style="width: 300px;padding:5px;border-right: 1px solid #ccc;border-bottom: 1px solid #ccc;text-align: left;font-weight: bold;font-size: 12px">Description</td>
                                    <td style="width: 100px;padding:5px;border-right: 1px solid #ccc;border-bottom: 1px solid #ccc;text-align: left;font-weight: bold;font-size: 12px">Qty</td>
                                    <td style="width: 100px;padding:5px;border-right: 1px solid #ccc;border-bottom: 1px solid #ccc;text-align: left;font-weight: bold;font-size: 12px">Unit Price</td>
                                    <td style="width: 100px;padding:5px;border-bottom: 1px solid #ccc;text-align: left;font-weight: bold;font-size: 12px">Total</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $dataInvoiceDetails  = DB::table('invoice_item_details')->where('invoice_id', $invoice['id'])->get();
                                $dataInvoiceDetails = json_decode(json_encode($dataInvoiceDetails));
                                foreach ($dataInvoiceDetails as $k => $v) {    ?>
                                    <tr style="border-bottom: 1px solid #ccc;height: 39px;">
                                        <td style="padding:5px;text-align: center;border-right: 1px solid #ccc;border-bottom: 1px solid #ccc;text-align: left;">
                                            <div><?php echo $v->fees_name_desc; ?></div>
                                        </td>
                                        <td style="padding:5px;text-align: center;border-right: 1px solid #ccc;border-bottom: 1px solid #ccc;text-align: right;">
                                            <div><?php echo number_format($v->quantity, 2); ?></div>
                                        </td>
                                        <td style="padding:5px;text-align: center;border-right: 1px solid #ccc;border-bottom: 1px solid #ccc;text-align: right;">
                                            <div><?php echo number_format($v->unit_price, 2); ?></div>
                                        </td>
                                        <td style="padding:5px;text-align: right;border-bottom: 1px solid #ccc;">
                                            <div><?php echo number_format($v->total_of_items, 2); ?></div>
                                        </td>
                                    </tr>
                                <?php  }  ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div style="float: left;width: 100%;margin-top: 10px">
                    <div style="width: 50%;float: left">
                        <div class="width:100%"><b>Memo</b></div>
                        <div class="width:100%"><?php echo !empty($invoice['memo']) ? $invoice['memo'] : '-' ?></div>
                        <div style="width: 70%;margin-top: 40%;margin-bottom: 2%;border-bottom: 1px solid #ccc;"></div>
                        <div style="width: 50%;">Signature Autorisee</div>
                    </div>
                    <div style="width: 50%;float: left;">
                        <div style="float: left;width: 100%;border-bottom: 1px solid #ccc;margin: 0px;padding: 5px">
                            <div style="float:left;width: 35%;text-align: left;">Sous Total</div>
                            <div style="float: left;width: 55%;margin-top: 0px;text-align: right;"><?php echo number_format($invoice['sub_total'], 2) ?></div>
                            <div style="float:left;margin-left: 2px"><?php $data = app('App\Currency')::getData($invoice['currency']);
                                                                        echo !empty($data->code) ? strtoupper($data->code) : "-"; ?></div>
                        </div>

                        <div style="float: left;width: 100%;border-bottom: 1px solid #ccc;margin: 0px;padding: 5px">
                            <div style="float:left;width: 35%;text-align: left;">TCA</div>
                            <div style="float: left;width: 55%;margin-top: 0px;text-align: right;">
                                <?php echo number_format($invoice['tca'], 2); ?></div>
                            <div style="float:left;margin-left: 2px"><?php $data = app('App\Currency')::getData($invoice['currency']);
                                                                        echo !empty($data->code) ? strtoupper($data->code) : "-"; ?></div>
                        </div>


                        <div style="float: left;width: 100%;border-bottom: 1px solid #ccc;margin: 0px;padding: 5px">
                            <div style="float:left;width: 35%;text-align: left;">Total</div>
                            <div style="float: left;width: 55%;margin-top: 0px;text-align: right;">
                                <?php echo number_format($invoice['total'], 2); ?></div>
                            <div style="float:left;margin-left: 2px"><?php $data = app('App\Currency')::getData($invoice['currency']);
                                                                        echo !empty($data->code) ? strtoupper($data->code) : "-"; ?></div>
                        </div>

                        <div style="float: left;width: 100%;border-bottom: 1px solid #ccc;margin: 0px;padding: 5px">
                            <div style="float:left;width: 35%;text-align: left;">Paiements / Credits</div>
                            <div style="float: left;width: 55%;margin-top: 0px;text-align: right;">
                                <?php echo number_format($invoice['credits'], 2); ?></div>
                            <div style="float:left;margin-left: 2px"><?php $data = app('App\Currency')::getData($invoice['currency']);
                                                                        echo !empty($data->code) ? strtoupper($data->code) : "-"; ?></div>
                        </div>

                        <div style="float: left;width: 100%;border-bottom: 1px solid #ccc;margin: 0px;padding: 5px">
                            <div style="float:left;width: 35%;text-align: left;">Solde du</div>
                            <div style="float: left;width: 55%;margin-top: 0px;text-align: right;">
                                <?php echo number_format($invoice['balance_of'], 2); ?></div>
                            <div style="float:left;margin-left: 2px"><?php $data = app('App\Currency')::getData($invoice['currency']);
                                                                        echo !empty($data->code) ? strtoupper($data->code) : "-"; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>