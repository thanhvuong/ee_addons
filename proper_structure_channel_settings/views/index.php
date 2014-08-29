
<style>
	.mainTable tbody tr:hover td{ color: #fff; background-color: #d91350!important; }
	.selectstyle { height: 25px; }
</style>
<script>
$(function(){$("#dialog-form").hide();$("#cSet tr").on("click",function(){var e=$(this).attr("data-cid");var t=$(this).attr("data-ct");var n=$(this).attr("data-type");var r=$(this).attr("data-tid");var i=$(this).attr("data-sa");var s=$(this).attr("data-sips");var o=$(this).attr("data-sid");$("#hcid").val(e);$("#sid").val(o);$("#ct").val(t);$("#type").val(n);$("#template").val(r);$("#sa").val(i);$("#sips").val(s);$("#dialog-form").dialog("open")});$("#dialog-form").dialog({autoOpen:false,height:500,width:400,modal:true,buttons:{Save:function(){$("#upd_scs").submit();$(this).dialog("close")},Cancel:function(){$(this).dialog("close")}},close:function(){}})})
</script>
<table id="cSet" class="mainTable">
	<thead>
		<th data-table-column="channels">Channels</th>
		<th data-table-column="type">Type</th>
		<th data-table-column="split_assets">Split Assets</th>
		<th data-table-column="show_in_selector">Show In Selector</th>
		<th data-table-column="template">Template</th>
	</thead>
	<tbody>
		<?php 
		foreach($c as $r)
		{
			echo '<tr data-cid="'.$r->cid.'" data-ct="'.$r->ct.'" data-type="'.$r->type.
			'" data-sa="'.$r->sa.'" data-sips="'.$r->sips.'" data-tid="'.$r->tid.'" data-sid="'.$r->sid.'">';
			echo '<td>'.$r->ct.'</td>';
			echo '<td>'.$r->type.'</td>';
			echo '<td>'.$r->sa.'</td>';
			echo '<td>'.$r->sips.'</td>';
			echo '<td>'.$r->tp.'</td>';		
			echo '</tr>';
		}
		?>
	</tbody>
</table>
<div id="dialog-form" title="Structure Channel Settings">
	<?=form_open($fa, array('id' => 'upd_scs'))?>
		<input type="hidden" id="hcid" name="hcid" value="" />
		<input type="hidden" id="sid" name="sid" value="" />

		<label for="ct">Channel Title</label>
		<input type="text" name="ct" id="ct" class="" style="margin:10px 0px;" disabled="disabled" />

		<label for="type">Type</label>
		<br/>
		<select name="type" id="type" class="selectstyle" style="margin:10px 0px;" />
			<?php 
				foreach($ty AS $tydd)
				{
					echo '<option value="'.$tydd.'">'.$tydd.'</option>';
				}
			?>
		</select>
		<br/>

		<div id="split_assets">
			<label for="sa">Split Assets</label>
			<br/>
			<select name="sa" id="sa" class="selectstyle" style="margin:10px 0px;" />
				<option value="n">n</option>
				<option value="y">y</option>
			</select>
			<br/>
		</div>

		<label for="sips">Show in Selector</label>
		<br/>
		<select name="sips" id="sips" class="selectstyle" style="margin:10px 0px;" />
			<option value="n">n</option>
			<option value="y">y</option>
		</select>
		<br/>

		<label for="template">Template</label><br/>
		<select name="template" id="template" class="selectstyle" style="margin:10px 0px;" />
			<option value="0"></option>
			<?php 
				foreach($t AS $tdd)
				{
					echo '<option value="'.$tdd->tid.'">'.$tdd->tp.'</option>';
				}
			?>
		</select>

	<?=form_close()?>
</div>