 <?php
    use App\ccpack; 
    $ccpackData = ccpack::getccpackdetail($ccpackId);
    $invoiceData = ccpack::getinvoicedetail($ccpackId);
    $items = ccpack::getinvoiceitemdetail($invoiceData->id);
    for ($i = 0;$i<=2;$i++){
        echo View::make('ccpackinvoices.multiprint',array('ccpackId'=>$ccpackId,'invoiceData'=>$invoiceData,'items'=>$items,'ccpackData'=>$ccpackData)) . '<br><hr>';
    }  
?>



<script type="text/javascript">
    window.focus(window.close());
</script>