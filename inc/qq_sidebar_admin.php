<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<? if(class_exists('booked_plugin')) {//проверяем на существование класса плагина booked_plugin. если есть значит плагин активирован
	$calendars = get_terms('booked_custom_calendars',array('orderby'=>'name','order'=>'ASC','hide_empty'=>false)); 
	if(isset($_GET["var"]) && $_GET["var"])
	{
		if(isset($_GET["val"]) && $_GET["val"])
		{
			update_option($_GET["var"], $_GET["val"]);
		}
	}?> 
<br>
<div class="container-fluid">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-puzzle-piece"></i> Параметры API интеграции для стороннего сайта</h3>
      </div>
      <div class="panel-body">
        <fieldset>
			<div id="extension">
				<fieldset>
					<legend>Ссылки на расписания квестов</legend>
					<div class="table-responsive">
						<table class="table table-bordered table-hover">
							<thead>
								<tr>
									<td class="text-left">Название</td>
									<td class="text-left">Ссылка</td>
									<td class="text-left">Цена</td>
									<td class="text-right">Действие</td>
									
								</tr>
							</thead>
							<tbody>
							<? foreach($calendars as $key => $value){
								add_option('qq_price_'.$calendars[$key]->slug,'0');?>
								<tr>
									<td class="text-left"><b><?echo $calendars[$key]->name;?></b></td>
									<td class="text-left"><b><?echo QQ_URI;?>inc/qq_timetable.php?quest=<?echo $calendars[$key]->slug;?></b></td>
									<td class="text-left">
										<div class="input-group">
											<input type="text" class="form-control" id="input_<? echo $calendars[$key]->slug;?>" aria-label="Amount (to the nearest dollar)" value="<? echo get_option('qq_price_'.$calendars[$key]->slug);?>">
											<span class="input-group-addon"> Руб.</span>
										</div>
									</td>
									<td class="text-right" id="a_<? echo $calendars[$key]->slug;?>">
										<a href="" data-toggle="tooltip" title="" class="btn btn-success" data-original-title="Активировать"><i class="fa fa-plus-circle">+</i></a>
									</td>
								</tr>
							<? } ?>
							</tbody>
						</table>
					</div>
				</fieldset>
				<fieldset>
					<legend>Ссылки на бронирование квестов</legend>
					<div class="table-responsive">
						<table class="table table-bordered table-hover">
							<thead>
								<tr>
									<td class="text-left">Название</td>
									<td class="text-left">Ссылка</td>
									<td class="text-right">Действие</td>
								</tr>
							</thead>
							<tbody>
							<? foreach($calendars as $key => $value){?>
								<tr>
									<td class="text-left"><b><?echo $calendars[$key]->name;?></b></td>
									<td class="text-left"><b><?echo QQ_URI;?>inc/qq_reservation.php?quest=<?echo $calendars[$key]->slug;?></b></td>
									<td class="text-right">
										<?/*<a href="" data-toggle="tooltip" title="" class="btn btn btn-danger btn-success" data-original-title="Активировать"><i class="fa fa-plus-circle">-</i></a> */?>
									</td>
								</tr>
							<? } ?>
							</tbody>
						</table>
					</div>
				</fieldset>
			</div>
		</div>
    </div>
 </div>
<?} else{ ?>
<h2>Плагин "booked calendar" не установлен или неактивен, интеграция не возможна.</h2>
<? }?>
<script>
jQuery(document).ready(function(){
<?foreach($calendars as $key => $value){?>
jQuery("#input_<? echo $calendars[$key]->slug;?>").blur(function() {
	var link = jQuery("#input_<? echo $calendars[$key]->slug;?>").val();
	var href = "<?php echo $_SERVER['PHP_SELF']; ?>?page=qq-ru&var=qq_price_<?echo $calendars[$key]->slug;?>&val=";
	jQuery("#a_<? echo $calendars[$key]->slug;?>").html('<a href="'+href+link+'" data-toggle="tooltip" title="" class="btn btn-primary" data-original-title="Сохранить"><i class="fa fa-plus-circle">+</i></a>')
	});
<?}?>
});
</script>