<?
define('MAIN_DIR', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
require_once( MAIN_DIR.'/wp-load.php' );
header('Content-Type: application/json');
header('Access-Control-Allow-Origin:*');

	if(isset($_GET["quest"]) && $_GET["quest"])
	{
		$slug=esc_attr($_GET["quest"]);
		
		if(isset($_POST["date"]) && $_POST["date"]){
			/****************************************************************************************************/
			if(date( 'Y-m-d', strtotime($_POST["date"]) ) == $_POST["date"])//проверка даты
				{$date=date('d.m.Y', strtotime($_POST["date"]));}
			else
				{echo '{"success":false, "message": "Неверно указана дата"}'; exit;}

			if(isset($_POST["time"]) && $_POST["time"]){//проверка времени
				if(date( 'H:i', strtotime($_POST["time"])) == $_POST["time"])
					{$time = date( 'H:i', strtotime($_POST["time"]));}
				else
					{echo '{"success":false, "message": "Неверно указано время"}'; exit;}	
			}
			else
				{echo '{"success":false, "message": "Неверно указана время"}'; exit;}
			
			if(isset($_POST["first_name"]) && $_POST["first_name"])//проверка имени
				{$name = esc_attr($_POST["first_name"]);}
			else
				{echo '{"success":false, "message": "Не указано имя"}'; exit;}
			
			if(isset($_POST["email"]) && $_POST["email"]){//проверка mail
				if(is_email($_POST["email"]))
					$email = esc_attr($_POST["email"]);
				else
					{echo '{"success":false, "message": "Неверно указан email"}'; exit;}
				}
			else
				{echo '{"success":false, "message": "Не указано email"}'; exit;}
			
			if(isset($_POST["phone"]) && $_POST["phone"])//проверка телефона
				{$phone_txt = esc_attr($_POST["phone"]);}
			else
				{echo '{"success":false, "message": "Не указан телефон"}'; exit;}
			
			if(isset($_POST["family_name"]) && $_POST["family_name"])//проверка имени
				{$family_name = esc_attr($_POST["family_name"]);}
			else{$family_name='';}
			
			if(isset($_POST["comment"]) && $_POST["comment"])//проверка коментов
				{$comment = esc_attr($_POST["comment"]);}
			else{$comment='';}
			
			if(isset($_POST["source"]) && $_POST["source"])//проверка источника
				{$source = esc_attr($_POST["source"]);}
			else{$source='';}
			
			if(isset($_POST["md5"]) && $_POST["md5"])//проверка md5
				{$md5 = esc_attr($_POST["md5"]);}
			else{$md5='';}
			
			if(isset($_POST["price"]) && $_POST["price"])//проверка цены
				{$price = esc_attr($_POST["price"]);}
			else{$price='';}
			/*****************************************************************************************************/
		}else{ echo '{"success":false, "message": "Не указана дата"}'; exit;}
		
	}else{exit;}
	
	/*
	$_POST["first_name"];// имя клиента
	$_POST["family_name"];// фамилия клиента (придет значение “(фамилия отсутствует)”)
	$_POST["phone"];// телефон клиента
	$_POST["email"];// email клиента
	$_POST["comment"];// комментарий от клиента
	$_POST["source"];// ‘questquest.ru’
	$_POST["md5"];// md5 код от строки ИмяФамилияТелефонEmailMd5code. Наличие этого поля позволяет проверять вам, что именно мы отправили вам бронирование. После завершения создания скриптов не забудьте прислать md5code. Если вы не будете делать подобную проверку, просто пропустите этот пунк, он необязательный.
	$_POST["date"];// дата игры (YYYY-mm-dd)
	$_POST["time"];// время игры (HH:MM)
	$_POST["price"];// цена игры
	*/
	

	$timestamp="0000000000000";
	$display_calendar_id;//id календаря
	$time_format = get_option('time_format');
	$date_format = get_option('date_format');
	$access=false;
	$main_timeslot="0000";
	
	$phone='<p class="cf-meta-value"><strong>телефон для подтверждения заявки: </strong>'.$phone_txt.'</p>
	<br><p><b>Фамилия: </b>'.$family_name.'</p>
	<br><p><b>Комментарий: </b>'.$comment.'</p>
	<br><p><b>Цена: </b>'.$price.'</p>
	<p><b>Заявка с: </b>'.$source.'</p>';
/**********************************************************************_календарь_******************************************************************************/
		
		$calendars = get_terms('booked_custom_calendars',array('orderby'=>'name','order'=>'ASC','hide_empty'=>false));//все календари в базе
		foreach($calendars as $key => $value){
				if($calendars[$key]->slug == $slug) {$display_calendar_id=$calendars[$key]->term_id;}//узнаем id календаря по имени
		}
		
		if(!$display_calendar_id){echo '{"success":false, "message": "Такого квеста нет"}'; exit;}
		
		if (!empty($calendars)):
			$tabbed = true;
			if (booked_user_role() == 'booked_booking_agent'):
				global $current_user;
				$calendars = booked_filter_agent_calendars($current_user,$calendars);	
			endif;
		else :
			$tabbed = false;
		endif;
	
/***************************************************************************************************************************************************************/
						
	if(booked_admin_calendar_date_loop_qq_res($date,$time, $time_format,$date_format,$display_calendar_id,$tabbed,$calendars, $slug, $main_timeslot, $timestamp))//смотрит возможно забронировать место на указаную дату и время
		if(add_qq_guest($date, $time, $time_format, $date_format, $display_calendar_id, $main_timeslot, $name, $email, $phone, $timestamp))
			echo '{"success": true}';
		else {echo'{"success":false, "message": "Неизвестная ошибка"}'; exit;}
	else 
		echo '{"success":false, "message": "В заданное время квеста нет"}'; 
?>


<?
function booked_admin_calendar_date_loop_qq_res($date,$time, $time_format,$date_format,$calendar_id = false,$tabbed = false,$calendars = false, $n_slug, &$main_timeslot, &$main_timestamp){//функция вывода списка расписания на указаный день
	
	$table_data = date_i18n("Y-m-d",strtotime($date));
	$table_time = "00:00";
	$table_is_free = false;
	
	$year = date('Y',strtotime($date));//год
	$month = date('m',strtotime($date));//месяц
	$day = date('d',strtotime($date));//день
	$day_name = date('D',strtotime($date));// день недели Mon
	$appointments_array = booked_get_admin_appointments_qq_res($date,$time_format,$date_format,$calendar_id,$tabbed,$calendars); //возвращаем масив записей c бронированием на этот день
	
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
			Считает количество доступных мест на каждую временную запись
			*/

			$spots_available = $count - count($appts_in_this_timeslot);
			$spots_available = ($spots_available < 0 ? $spots_available = 0 : $spots_available = $spots_available);

			/*
			Отображение временного интервала
			*/

			$timeslot_parts = explode('-',$timeslot);
			$current_timestamp = current_time('timestamp');
			$this_timeslot_timestamp = strtotime($year.'-'.$month.'-'.$day.' '.$timeslot_parts[0]);
			$main_timestamp = $this_timeslot_timestamp;

			if ($current_timestamp < $this_timeslot_timestamp){
				$table_is_free = $available = true;
			} else {
				$table_is_free = $available = false;
			}

			if ($timeslot_parts[0] == '0000' && $timeslot_parts[1] == '2400'):
				echo $timeslotText = __('All day','booked');
			else :
				$timeslotText = date_i18n($time_format,strtotime($timeslot_parts[0])).' &ndash; '.date_i18n($time_format,strtotime($timeslot_parts[1]));
				
				$table_time = date_i18n($time_format,strtotime($timeslot_parts[0]));//НАШЕ ВРЕМЯ!!!!!!!
				
			endif;


			if($table_time == $time)
			{
				if($spots_available>0)
					{$main_timeslot=$timeslot; return true;}
				else if($spots_available==0)
					{echo '{"success":false, "message": "Нет свободных мест"}'; exit;}
			}

			
		endforeach;


	/*
	Нет временных интервалов по умолчанию, но есть назначения, заказанные.
	*/

	} else if (!$todays_defaults && !empty($appointments_array)) {echo '{"success":false, "message": "На этот день нет расписания"}'; exit;}

	/*
	Нет временных интервалов по умолчанию и никаких назначений за определенный день.
	*/
	else {echo '{"success":false, "message": "На этот день нет расписания"}'; exit;}
	
	return false;
}




/***********************************************************************************************************************************************************************/






function booked_get_admin_appointments_qq_res($date,$time_format,$date_format,$calendar_id = false,$tabbed = false,$calendars = false){ //Функция возвращения записей
	
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

//add_qq_guest(date_format($date, 'd.m.Y'), $time, $time_format, $date_format, $display_calendar_id, $main_timeslot, $name, $email, $phone);

function add_qq_guest($date, $time, $time_format, $date_format, $calendar_id = false, $timeslot, $post_name, $post_email, $post_phone, $timestamp){

		do_action('booked_before_creating_appointment');
		$phone = $post_phone;
		$new_fiel="single-line-text-label---8184298___required";

		$calendar_id_for_cf = $calendar_id;
		if ($calendar_id):
			$calendar_id = array($calendar_id);
			$calendar_id = array_map( 'intval', $calendar_id );
			$calendar_id = array_unique( $calendar_id );
		endif;
		
		$appointment_default_status = get_option('booked_new_appointment_default','draft');

		
/**********************_RUN_************************/
		
			$name = $post_name;
			$email =$post_email;
			if (is_email($email) && $name):

				// Create a new appointment post for a guest customer
				$new_post = apply_filters('booked_new_appointment_args', array(
					'post_title' => date_i18n($date_format,$timestamp).' @ '.date_i18n($time_format,$timestamp).' (User: Guest)',
					'post_content' => '',
					'post_status' => $appointment_default_status,
					'post_date' => date('Y',strtotime($date)).'-'.date('m',strtotime($date)).'-01 00:00:00',
					'post_type' => 'booked_appointments'
				));
				$post_id = wp_insert_post($new_post);
	
				update_post_meta($post_id, '_appointment_guest_name', $name);
				update_post_meta($post_id, '_appointment_guest_email', $email);
				update_post_meta($post_id, '_appointment_timestamp', $timestamp);
				update_post_meta($post_id, '_appointment_timeslot', $timeslot);
	
				if (apply_filters('booked_update_cf_meta_value', true)) {
					update_post_meta($post_id, '_cf_meta_value', $phone);
				}
	
				if (apply_filters('booked_update_appointment_calendar', true)) {
					if (!empty($calendar_id)): $calendar_term = get_term_by('id',$calendar_id[0],'booked_custom_calendars'); $calendar_name = $calendar_term->name; wp_set_object_terms($post_id,$calendar_id,'booked_custom_calendars'); else: $calendar_name = false; endif;
				}
	
				do_action('booked_new_appointment_created', $post_id);
	
				$timeslots = explode('-',$timeslot);
	
				$timestamp_start = strtotime('2015-01-01 '.$timeslots[0]);
				$timestamp_end = strtotime('2015-01-01 '.$timeslots[1]);
		
				if ($timeslots[0] == '0000' && $timeslots[1] == '2400'):
					$timeslotText = __('All day','booked');
				else :
					$timeslotText = date_i18n($time_format,$timestamp_start).'&ndash;'.date_i18n($time_format,$timestamp_end);
				endif;
	
				// Send an email to the Admin?
				$email_content = get_option('booked_admin_appointment_email_content');
				$email_subject = get_option('booked_admin_appointment_email_subject');
				if ($email_content && $email_subject):
					$admin_email = booked_which_admin_to_send_email($_POST['calendar_id']);
					$tokens = array('%name%','%date%','%time%','%customfields%','%calendar%','%email%');
					$replacements = array($name,date_i18n($date_format,$timestamp),$timeslotText,$phone,$calendar_name,$email);
					$email_content = htmlentities(str_replace($tokens,$replacements,$email_content), ENT_QUOTES | ENT_IGNORE, "UTF-8");
					$email_content = html_entity_decode($email_content, ENT_QUOTES | ENT_IGNORE, "UTF-8");
					$email_subject = str_replace($tokens,$replacements,$email_subject);
					booked_mailer( $admin_email, $email_subject, $email_content );
				endif;
	
				return true;
				
			else :
				return false;
			endif;
}
?>