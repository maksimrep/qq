<? 
define('MAIN_DIR', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
require_once( MAIN_DIR.'/wp-load.php' );
header('Content-Type: application/json');
header('Access-Control-Allow-Origin:*');
	if(isset($_GET["quest"]) && $_GET["quest"])
	{
		$slug=$_GET["quest"];
	}else{
		exit;
	}
		$id;
		
		
		$calendars = get_terms('booked_custom_calendars',array('orderby'=>'name','order'=>'ASC','hide_empty'=>false));
		foreach($calendars as $key => $value){
				if($calendars[$key]->slug == $slug) $id=$calendars[$key]->term_id;
		}
		$timetable = get_option('booked_defaults_'.$id);

/***************************************************************************************************************************************************************/

	
	$date=date_create(current_time('d.m.Y'));
	$json_data;
	if (!empty($calendars)):
		$tabbed = true;
		if (booked_user_role() == 'booked_booking_agent'):
			global $current_user;
			$calendars = booked_filter_agent_calendars($current_user,$calendars);	
		endif;
	else :
		$tabbed = false;
	endif;

$display_calendar_id = $id;
$time_format = get_option('time_format');
$date_format = get_option('date_format');

					for($i=0; $i<14; $i++)
					{
						
						$between=booked_admin_calendar_date_loop(date_format($date, 'd.m.Y'),$time_format,$date_format,$display_calendar_id,$tabbed,$calendars, $slug);//выводит список расписания на указаный день
						if($between!='')
							$json_data.=$between;
						date_modify($date, '+1 day');
					}
					$json_data=rtrim($json_data, ',');
echo '['.$json_data.']';

function booked_admin_calendar_date_loop($date,$time_format,$date_format,$calendar_id = false,$tabbed = false,$calendars = false, $n_slug){//функция вывода списка расписания на указаный день
	
	$table;
	$table_data = date_i18n("Y-m-d",strtotime($date));
	$table_time = "00:00";
	$table_is_free = false;
	$table_price=get_option('qq_price_'.$n_slug);
	$table_i=0;
	
	$year = date('Y',strtotime($date));//год
	$month = date('m',strtotime($date));//месяц
	$day = date('d',strtotime($date));//день
	$date_display = date_i18n($date_format,strtotime($date));//выводит дату в виде 23.07.2017
	$day_name = date('D',strtotime($date));// день недели Mon
	$appointments_array = booked_get_admin_appointments($date,$time_format,$date_format,$calendar_id,$tabbed,$calendars); //возвращаем масив записей
	
	/*
	Начало списка
	*/
	//echo '<h2><strong>'.$date_display.'</strong></h2>'; //выводит текущую дата

	/*
	Получить сегодняшние временные интервалы
	*/

	if ($calendar_id):
		$booked_defaults = get_option('booked_defaults_'.$calendar_id);
		if (!$booked_defaults):
			$booked_defaults = get_option('booked_defaults');
		endif;
	else :
		$booked_defaults = get_option('booked_defaults');
	endif;

	$formatted_date = date('Ymd',strtotime($date));
	$booked_defaults = booked_apply_custom_timeslots_filter($booked_defaults,$calendar_id);//функция вызывается из wp-content\plugins\booked\includes\general-functions.php возвращает масив расписания
	if (isset($booked_defaults[$formatted_date]) && !empty($booked_defaults[$formatted_date])):
		$todays_defaults = is_array($booked_defaults[$formatted_date]) ? $booked_defaults[$formatted_date] : json_decode($booked_defaults[$formatted_date],true);
	elseif (isset($booked_defaults[$formatted_date]) && empty($booked_defaults[$formatted_date])):
		$todays_defaults = false;
	elseif (isset($booked_defaults[$day_name]) && !empty($booked_defaults[$day_name])):
		$todays_defaults = $booked_defaults[$day_name];
	else :
		$todays_defaults = false;
	endif;
	

	/*
	Доступны временные интервалы, давайте прокрутим их
	*/

	if ($todays_defaults){

		ksort($todays_defaults);

		foreach($todays_defaults as $timeslot => $count):

			$appts_in_this_timeslot = array();

			/*
			Есть ли назначения в этом конкретном временном интервале?
			Если да, то создадим массив из них.
			*/

			foreach($appointments_array as $post_id => $appointment):
				if ($appointment['timeslot'] == $timeslot):
					$appts_in_this_timeslot[] = $post_id;
				endif;
			endforeach;

			/*
			Рассчитать количество доступных мест в зависимости от общего количества минус назначенных встреч
			*/

			$spots_available = $count - count($appts_in_this_timeslot);
			$spots_available = ($spots_available < 0 ? $spots_available = 0 : $spots_available = $spots_available);

			/*
			Отображение временного интервала
			*/

			$timeslot_parts = explode('-',$timeslot);
			$current_timestamp = current_time('timestamp');
			$this_timeslot_timestamp = strtotime($year.'-'.$month.'-'.$day.' '.$timeslot_parts[0]);

			if ($current_timestamp < $this_timeslot_timestamp && $spots_available > 0){
				$table_is_free = $available = true;
			} else {
				$table_is_free = $available = false;
			}

			if ($timeslot_parts[0] == '0000' && $timeslot_parts[1] == '2400'):
				$timeslotText = __('All day','booked');
			else :
				$timeslotText = date_i18n($time_format,strtotime($timeslot_parts[0])).' &ndash; '.date_i18n($time_format,strtotime($timeslot_parts[1]));
				
				$table_time = date_i18n($time_format,strtotime($timeslot_parts[0]));//НАШЕ ВРЕМЯ!!!!!!!
				
			endif;


			$new=json_encode(array("date" => $table_data, "time" => $table_time, "is_free" => $table_is_free, "price" => $table_price));
			$table.=$new.',';


					/*
					Отобразить назначеных встречь в данном времени
					*/
					//if (!empty($appts_in_this_timeslot)):
					//endif;


		endforeach;

		/*
		Есть ли какие-либо дополнительные встречи на этот день, которые не входят в временные интервалы по умолчанию?
		*/
		//if (!empty($appointments_array)):
		//endif;

	/*
	Нет временных интервалов по умолчанию, но есть назначения, заказанные.
	*/

	} else if (!$todays_defaults && !empty($appointments_array)) {
		return 0;

	/*
	Нет временных интервалов по умолчанию и никаких назначений за определенный день.
	*/
	} else {return 0;}
	
	return $table;
}




/***********************************************************************************************************************************************************************/






function booked_get_admin_appointments($date,$time_format,$date_format,$calendar_id = false,$tabbed = false,$calendars = false){ //Функция возвращения записей
	
	$year = date('Y',strtotime($date));
	$month = date('m',strtotime($date));
	$day = date('d',strtotime($date));

	$start_timestamp = strtotime($year.'-'.$month.'-'.$day.' 00:00:00');
	$end_timestamp = strtotime($year.'-'.$month.'-'.$day.' 23:59:59');

	$args = array(
		'post_type' => 'booked_appointments',
		'posts_per_page' => -1,
		'post_status' => 'any',
		'meta_query' => array(
			array(
				'key'     => '_appointment_timestamp',
				'value'   => array( $start_timestamp, $end_timestamp ),
				'compare' => 'BETWEEN'
			)
		)
	);

	if ($calendar_id && $calendar_id != 'default'):
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'booked_custom_calendars',
				'field'    => 'id',
				'terms'    => $calendar_id,
			)
		);
	elseif (!$calendar_id && $tabbed && !empty($calendars) || $calendar_id = 'default'):

		$not_in_calendar = array();
	
		foreach($calendars as $calendar_term){
            $not_in_calendar[] = $calendar_term->term_id;
        }

		$args['tax_query'] = array(
			array(
				'taxonomy' 			=> 'booked_custom_calendars',
				'field'    			=> 'id',
				'terms'            	=> $not_in_calendar,
				'include_children' 	=> false,
				'operator'         	=> 'NOT IN'
			)
		);

	endif;

	$appointments_array = array();

	$bookedAppointments = new WP_Query($args);
	if($bookedAppointments->have_posts()):
		while ($bookedAppointments->have_posts()):
			$bookedAppointments->the_post();
			global $post;
			$timestamp = get_post_meta($post->ID, '_appointment_timestamp',true);
			$timeslot = get_post_meta($post->ID, '_appointment_timeslot',true);
			$day = date('d',$timestamp);
			
			$guest_name = get_post_meta($post->ID, '_appointment_guest_name',true);
			$guest_email = get_post_meta($post->ID, '_appointment_guest_email',true);
			
			$appointments_array[$post->ID]['post_id'] = $post->ID;
			$appointments_array[$post->ID]['timestamp'] = $timestamp;
			$appointments_array[$post->ID]['timeslot'] = $timeslot;
			$appointments_array[$post->ID]['status'] = $post->post_status;
			
			if (!$guest_name):
				$user_id = get_post_meta($post->ID, '_appointment_user',true);
				$appointments_array[$post->ID]['user'] = $user_id;
			else:
				$appointments_array[$post->ID]['guest_name'] = $guest_name;
				$appointments_array[$post->ID]['guest_email'] = $guest_email;
			endif;
		
		endwhile;
		$appointments_array = apply_filters('booked_appointments_array', $appointments_array);
	endif;
	
	return $appointments_array;
	
}
/**************************************************************************************************************************************************************************/

?>