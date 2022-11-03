
<style type="text/css">

</style>

<div class="wrap">

	<div id="icon-options-general" class="icon32"></div>
	<h2><?php esc_attr_e( 'WT GeoLocation: Как настроить геотаргетинг?', 'wp_admin_style' ); ?></h2>

	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">

				<div class="meta-box-sortables ui-sortable">

					<div class="postbox">

						<h3><span><?php esc_attr_e( 'Примеры использования', 'wp_admin_style' ); ?></span></h3>

						<div class="inside">
							<p>
								Для создания условий геотаргетинга необходимо разместить Шорткод (или группу Шорткодов) в нужном Вам месте. Размещать Шорткоды можно как в текстовом редакторе, так и в коде шаблонов страницы. Ниже представлены несколько примеров.
							</p>
							<p>
								<strong>Условие 1:</strong> Выводим один из 3 телефонных номеров. Телефон 8(4912) 37-13-82 для города Рязань, телефон 8(499) 503-53-26 для города Москва, и телефон 8-800-496-0000 для всех остальных регионов. Шорткоды размещаем с помощью встроенного текстового редактора WordPress.<br>
								<ul>
									<li><code>[wt_geotargeting type="phone" city_show="Рязань"]8(4912) 37-13-82[/wt_geotargeting]</code></li>
									<li><code>[wt_geotargeting type="phone" city_show="Москва"]8(499) 503-53-26[/wt_geotargeting]</code></li>
									<li><code>[wt_geotargeting type="phone" default=true]8-800-496-0000[/wt_geotargeting]</code></li>
								</ul>

							</p>
							<p>
								<strong>Условие 2:</strong> Выводим один из 2 телефонных номеров. Телефон 8-927-867-6525 для Самарской области, телефон 8-936-689-8579 для Московской области. Шорткоды размещаем с помощью встроенного текстового редактора WordPress.<br>
								<ul>
									<li><code>[wt_geotargeting type="phone_mobile" region_show="Самарская область"]8-927-867-6525[/wt_geotargeting]</code></li>
									<li><code>[wt_geotargeting type="phone_mobile" region_show="Московская область"]8-936-689-8579[/wt_geotargeting]</code></li>
								</ul>
							</p>
							<p>
								<strong>Условие 3:</strong> Выводим один из 4 заголовков. Шорткоды размещаем в исходном коде шаблона страницы, с помощью PHP функции do_shortcode().
								<ul>
									<li><code>echo do_shortcode('[wt_geotargeting type="title" city_show="Волгоград"]Приветствуем жителей Волгограда![/wt_geotargeting]');</code></li>
									<li><code>echo do_shortcode('[wt_geotargeting type="title" city_show="Казань"]Приветствуем жителей Казани![/wt_geotargeting]');</code></li>
									<li><code>echo do_shortcode('[wt_geotargeting type="title" city_show="Москва"]Приветствуем жителей Москвы![/wt_geotargeting]');</code></li>
									<li><code>echo do_shortcode('[wt_geotargeting type="title" default=true]Приветствуем посетителей нашего сайта![/wt_geotargeting]');</code></li>
								</ul>
							</p>

						</div>


						<h3><span><?php esc_attr_e( 'Конструкция Шорткода для создания условий геотаргетинга:', 'wp_admin_style' ); ?></span></h3>


						<div class="inside">
						<code>[wt_geotargeting Атрибут="Значение атрибута" Атрибут_2="Значение атрибута 2" ...]Выводимый контент[/wt_geotargeting]</code>
						</div>

						<h3><span><?php esc_attr_e( 'Атрибуты для шорткода «wt_geotargeting»:', 'wp_admin_style' ); ?></span></h3>
						<div class="inside">
							<strong>type</strong> - Тип контента. <br>
							<strong>city_show</strong> - Условие "Совпадение города".<br>
							<strong>city_not_show</strong> - Условие "Несовпадение города".<br>
							<strong>region_show</strong> - Условие "Совпадение региона".<br>
							<strong>region_not_show</strong> - Условие "Несовпадение региона".<br>
							<strong>district_show</strong> - Условие "Совпадение округа".<br>
							<strong>district_not_show</strong> - Условие "Несовпадение округа".<br>
							<strong>country_show</strong> - Условие "Совпадение страны".<br>
							<strong>country_not_show</strong> - Условие "Несовпадение страны".<br>
							<strong>default</strong> - Значение по умолчанию. Рекомендуется использовать всегда, так как в случае отсутствия подключения к базе IP-адресов, значение Default выведется в обязательном порядке.<br>
							<strong>get</strong> — Вывод на экран значений региона пользователя. Доступные параметры атрибута: ip, country, city, region, district, lat, lng.

								<p><b style="color: green;">Внимание!</b> При написания условий совпадения страны, необходимо использовать буквенный код стран в формате «Альфа-2» (например <b>UA</b> для Украины, и <b>RU</b> для России).</p>
						</div>


						<h3><span><?php esc_attr_e( 'Примеры реализации выбора города', 'wp_admin_style' ); ?></span></h3>
						<div class="inside">
							<p>
								Для добавления на сайт возможности посетителю выбрать свой город, необходимо  разместить на нужной странице текст предложения и ссылки на города. Пример HTML кода:
							</p>
							<p>
								<code>
									Выберите ближайший к вам город:<br/>
									&lt;a href="?wt_region_by_default=Приморский+край"&gt;Владивосток&lt;/a&gt;,<br/>
									&lt;a class="btn btn-default" href="?wt_city_by_default=Москва"&gt;Москва&lt;/a&gt;,<br/>
									&lt;a href="?wt_region_by_default=Самарская+область"&gt;Тольятти&lt;/a&gt;
								</code>

							</p>
						</div>

						<h3><span><?php esc_attr_e( 'Принимаемые Get-переменные:', 'wp_admin_style' ); ?></span></h3>
						<div class="inside">
							<strong>wt_country_by_default  </strong>- Сохранение страны в cookie для дальнейшего использования. <br>
							<strong>wt_district_by_default  </strong>- Сохранение округа в cookie для дальнейшего использования.<br>
							<strong>wt_region_by_default </strong>- Сохранение региона в cookie для дальнейшего использования.<br>
							<strong>wt_city_by_default  </strong>- Сохранение города в cookie для дальнейшего использования.
						</div>

						<h3><span><?php esc_attr_e( 'Полезные ссылки:', 'wp_admin_style' ); ?></span></h3>
						<div class="inside">
							<a href="http://web-technology.biz/cms-wordpress/plugin-wt-geotargeting-for-cms-wordpress" target="_blank">Официальная страница плагина</a><br>
							<a href="https://wordpress.org/plugins/wt-geotargeting" target="_blank">Страница плагина на WordPress.org</a><br>
							<a href="https://vk.com/topic-40886935_33381010" target="_blank">Тема Вконтакте для обсуждения плагина</a><br>
							<a href="http://ipgeobase.ru/" target="_blank">База IP-адресов "IpGeoBase"</a>
						</div>

					</div>
					<!-- .postbox -->

				</div>
				<!-- .meta-box-sortables .ui-sortable -->

			</div>
			<!-- post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">

				<div class="meta-box-sortables">
					<div id="city_info" style="position: relative; top: -90px;"></div>

					<div class="postbox">

						<h3><span><?php esc_attr_e(
									'Справочник городов', 'wp_admin_style'
								); ?></span></h3>
						<div class="inside">
							<p>Выбрав город Вы можете посмотреть дополнительные параметры для составления условий геотаргетинга.</p>
							<form action="#city_info" method="get"> 
								<input type="hidden" name="page" value="<?php if (!empty($_GET['page'])) echo $_GET['page']; ?>"> <!-- Текущая страница -->
								<select name="ipgeobase_city_id">
									<?php
										foreach ($this->data->getCities() as $key => $value) { ?>
										<option value="<?php echo $key; ?>" 
											<?php 
											if (!empty($_GET['ipgeobase_city_id']) && $key == $_GET['ipgeobase_city_id']) 
												echo ' selected="selected" ';
											?>
											><?php echo $value; ?></option>
										<?php
										}
									?>
								</select>
								<input type="submit" value="Просмотр">
							</form>

							<?php
								if (!empty($_GET['ipgeobase_city_id'])){
									$city_info = $this->data->getCityInfo($_GET['ipgeobase_city_id']);
									?> <p> <?php	
									if (isset($city_info['city'])) echo '<b>Город:</b> '.$city_info['city'].'<br>';
									if (isset($city_info['region'])) echo '<b>Регион:</b> '.$city_info['region'].'<br>';
									if (isset($city_info['district'])) echo '<b>Округ:</b> '.$city_info['district'].'<br>';		
									?> </p> <?php
								}
							?>
						</div>
						
						<!-- .inside -->

					</div>
					<!-- .postbox -->

				</div>

				<div class="meta-box-sortables">

					<div class="postbox">

						<h3><span><?php esc_attr_e(
									'Тестирование работы плагина', 'wp_admin_style'
								); ?></span></h3>

						<div class="inside">								<?php 
									echo '<b>Ваш IP-адрес:</b> '.WT::$obj->geo->ip. '<br>';
								    echo '<b>Страна:</b> '.WT::$obj->geo->data['country']. '<br>';
								    echo '<b>Город:</b> '.WT::$obj->geo->data['city']. '<br>';
								    echo '<b>Регион:</b> '.WT::$obj->geo->data['region']. '<br>';
									echo '<b>Округ:</b> '.WT::$obj->geo->data['district']. '<br>';
								    echo '<b>Широта (Latitude, lat):</b> '.WT::$obj->geo->data['lat']. '<br>';
								    echo '<b>Долгота (Longitude, lng):</b> '.WT::$obj->geo->data['lng']. '<br>';
								?>
						</div>
						<!-- .inside -->

					</div>
					<!-- .postbox -->

				</div>
				<!-- .meta-box-sortables -->


				<div class="meta-box-sortables">

					<div class="postbox">

						<h3><span style="color: red;"><?php esc_attr_e(
									'Внимание!!!', 'wp_admin_style'
								); ?></span></h3>
						<div class="inside">
							
								Не смотря на успешность отображения на сайте контента ориентированного на местоположение пользователей, есть у геотаргетинга и <strong>обратная сторона - погрешность и невозможность определить местоположение посетителя</strong>. Это достаточно важный момент и его всегда стоит учитывать, иначе Вы можете потерять часть потенциальных клиентов. Мы рекомендуем реализовать структуру вашего сайта таким образом, что-бы пользователь в случае необходимости мог поменять своё месторасположение и найти необходимую контактную информацию.
							
						</div>
						
						<!-- .inside -->

					</div>
					<!-- .postbox -->

				</div>
				<!-- .meta-box-sortables -->



				<div class="meta-box-sortables">

					<div class="postbox">

						<h3><span><?php esc_attr_e(
									'Развитие плагина "WT GeoTargeting"', 'wp_admin_style'
								); ?></span></h3>
						<div class="inside">
							
								Вы можете оказать помощь в развитии плагина следующем образом:<br/>
								1) Предложить способы улучшения плагина или указать на существующие 
								ошибки на <a href="http://web-technology.biz/cms-wordpress/plugin-wt-geotargeting-for-cms-wordpress/">основной странице плагина</a>. <br/>
								2) Оставить отзыв на <a href="https://wordpress.org/support/view/plugin-reviews/wt-geotargeting">WordPress.org</a>.
							
						</div>
						
						<!-- .inside -->

					</div>
					<!-- .postbox -->

				</div>

			</div>
			<!-- #postbox-container-1 .postbox-container -->

		</div>
		<!-- #post-body .metabox-holder .columns-2 -->

		<br class="clear">
	</div>
	<!-- #poststuff -->

</div> <!-- .wrap -->