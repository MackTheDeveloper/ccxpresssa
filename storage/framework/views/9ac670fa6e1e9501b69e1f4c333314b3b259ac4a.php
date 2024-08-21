<?php if(auth()->guard()->guest()): ?>
<script type="text/javascript">
	location.reload();
</script>
<?php else: ?>


<div class="filesContainer">
	<?php echo e(Form::open(array('url' => $actionUrl,'class'=>'form-horizontal create-form','id'=>'createforms','autocomplete'=>'off','enctype'=>"multipart/form-data"))); ?>

	<?php echo e(csrf_field()); ?>



	<input type="hidden" name="id" value=<?php echo $id; ?>>
	<input type="hidden" name="count" id="count" value="">
	<table class="table table-file">
		<thead>
			<tr>
				<th>File Type</th>
				<th>File Name</th>
				<th>File</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<tr id="0" style="margin: 100%">
				<td>
					<?php echo Form::select('files[filetype][0]', Config::get('app.fileTypes'), '', ['class' => 'form-control selectpicker', 'data-live-search' => 'true', 'placeholder' => 'Select ...', 'id' => 'ft-0', 'style' => 'display:block !important']); ?>
				</td>
				<td>
					<?php echo Form::text('files[filename][0]', $file_number . '_', ['class' => 'form-control', 'id' => 'fn-0', 'readonly' => true]); ?>
				</td>
				<td>
					<?php echo Form::file('files[0]', ['class' => 'form-control files', 'id' => 'fv-0']); ?>
				</td>
				<td>
					<a href="javascript:void(0)" data-cid="0" class='btn btn-success btn-xs addmorefile'>+</a>
				</td>
			</tr>
		</tbody>
	</table>

	<div class="col-md-12">
		<button type="submit" class="btn btn-success">
			<?php
			echo "Upload";
			?>
		</button>
	</div>
	<?php echo e(Form::close()); ?>

</div>
<script type="text/javascript">
	$(document).ready(function() {
		var count = 0;
		var file_number = "<?php echo $file_number; ?>";
		$(".filesContainer").delegate(".addmorefile", "click", function() {
			count++;
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});
			var urlzte = '<?php echo url("file/addmorefile"); ?>';
			$.ajax({
				url: urlzte,
				type: 'POST',
				async: false,
				data: {
					'count': count,
					'file_number': file_number
				},
				success: function(response) {
					$('#modalUploadNewFiles .table-file tbody').append(response);
				}
			});
			$('#count').attr('value', count);
		});

		$(document).delegate(".removefile", "click", function() {
			var ids = $(this).data('cid');
			$("#" + ids).remove();
			count--;
			$('#count').attr('value', count);
		});

		$(document).delegate('.files', 'change', function() {
			var id = $(this).attr('id');
			var filename = ($(this).val().replace(/C:\\fakepath\\/i, ''));
			var arr = id.split('-');
			var val = ($('#fn-' + arr[1]).val()).split('_');
			var newVal = val[0] + '_';
			$('#fn-' + arr[1]).val(newVal + filename);
		});



		// $('#createforms').on('submit', function (event) {
		//        	event.preventDefault();
		//        	var formData = $('#createforms').serialize();

		//        	$.ajaxSetup({
		//                   headers:{
		//                       'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		//                   }
		//               });


		//               var urlz = '<?php echo $actionUrl; ?>';
		//               $.ajax({
		//                       url:urlz,
		//                       async:false,
		//                       type:'POST',
		//                       data:formData,
		//                       success:function(data) {
		//                              consol.log(data);

		//                               //location.reload();

		//                           },
		//                       });

		//        });	




		$('#createforms').on('submit', function(event) {

			$('.files').each(function() {

				$(this).rules("add", {
					required: true,
				})

			});


		});

		$('#createforms').validate({
			submitHandler: function(form) {
				$('#loading').show();
				$('#createforms')[0].submit();
			}
		});
	});
</script>
<?php endif; ?>