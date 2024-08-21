			
			<tr id= <?php echo $count;?> >
				<td>
					<?php echo Form::select('files[filetype]['.$count.']',Config::get('app.fileTypes'),'',['class'=>'form-control selectpicker','data-live-search' => 'true','placeholder' => 'Select ...','id'=>'ft-0','style'=>'display:block !important']); ?>
				</td>
				<td>
					<?php echo Form::text('files[filename]['.$count.']',$file_number.'_',['class'=>'form-control','id'=>'fn-'.$count,'readonly'=>true]);?>
				</td>
				<td>
					<?php echo Form::file('files['.$count.']',['class'=>'form-control files','id'=>'fv-'.$count]);?>
				</td>
				<td>
					<a href="javascript:void(0)" data-cid=<?php echo $count;?>  class='btn btn-success btn-xs addmorefile'>+</a>
					<a href="javascript:void(0)" data-cid=<?php echo $count;?>  class='btn btn-danger btn-xs removefile'>-</a>
				</td>
			</tr>


			
