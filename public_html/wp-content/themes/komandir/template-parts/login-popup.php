<div id="login-popup" class="custom-popup mfp-hide"></div>

<template id="login-send-code">
	<div class="custom-popup-section login-send-code">
		<h2>Войти или зарегистрироваться</h2>
		<form class="form-send-code" method="post">
			<label class="custom-popup-form-label">
				<input type="tel" placeholder="Телефон" name="phone" />
				<button class="input-tip" id="phone-tip" aria-label="Подсказка"></button>
			</label>
			<?php wp_nonce_field( 'komandir-send-code', 'komandir-send-code-nonce' ); ?>
			<button type="submit" class="custom-popup-submit form-send-code-submit">Получить код</button>
		</form>
		<p class="alter-text">Другие способы входа:</p>
		<a href="#" class="login-pass-link">Войти с паролем</a>
		<p class="policy-text">Нажимая кнопку "Получить код", Вы соглашаетесь c условиями
			<a href="/privacy-policy">политики конфиденциальности</a>
		</p>
	</div>
</template>

<template id="login-pass">
	<div class="custom-popup-section login-pass">
		<button class="login-popup-backlink" aria-label="Назад"></button>
		<h2>Войти с паролем</h2>
		<form class="form-pass" method="post">
			<label class="custom-popup-form-label">
				<input type="text" placeholder="Телефон или email" name="login" />
				<button class="input-tip" id="phone-tip" aria-label="Подсказка"></button>
			</label>
			<label class="custom-popup-form-label">
				<input type="password" placeholder="Пароль" name="pass" />
				<button class="show-pass" aria-label="Показать пароль"></button>
			</label>
			<a href="<?php echo wc_lostpassword_url(); ?>" class="recover-link">Забыли пароль?</a>
			<?php wp_nonce_field( 'komandir-pass', 'komandir-pass-nonce' ); ?>
			<button type="submit" class="custom-popup-submit form-pass-submit">Войти</button>
		</form>
	</div>
</template>

<template id="login-sms">
	<div class="custom-popup-section login-sms">
		<button class="login-popup-backlink" aria-label="Назад"></button>
		<h2>Введите SMS-код</h2>
		<form class="form-sms" method="post">
			<label class="custom-popup-form-label">
				<input type="text" placeholder="Код из SMS" name="sms" maxlength="4" autocomplete="off" />
			</label>
			<?php wp_nonce_field( 'komandir-sms', 'komandir-sms-nonce' ); ?>
			<button type="submit" class="custom-popup-submit form-sms-submit"></button>
		</form>
		<div class="counter-block"></div>
		<p class="alter-text">Другие способы входа:</p>
		<a href="#" class="login-pass-link">Войти с паролем</a>
	</div>
</template>

<template id="login-profile">
	<div class="custom-popup-section login-profile">
		<h2>Мой профиль</h2>
		<form class="form-profile" method="post" autocomplete="off">
			<label class="custom-popup-form-label">
				<input type="tel" placeholder="Телефон" name="login-phone" disabled />
			</label>
			<label class="custom-popup-form-label">
				<input type="email" placeholder="Email" name="login-email" />
			</label>
			<!-- <label class="custom-popup-form-label">
				<input type="text" placeholder="Логин" name="login-display-name" />
			</label> -->
			<label class="custom-popup-form-label">
				<input type="text" placeholder="Имя" name="login-firstname" />
			</label>
			<label class="custom-popup-form-label">
				<input type="text" placeholder="Фамилия" name="login-lastname" />
			</label>
			<div class="form-profile-pass-row">
				<label class="custom-popup-form-label">
					<input type="password" placeholder="Пароль" name="login-pass-first" autocomplete="off" />
					<button class="show-pass" aria-label="Показать пароль"></button>
				</label>
				<label class="custom-popup-form-label">
					<input type="password" placeholder="Подтвердить пароль" name="login-pass-second" autocomplete="off" />
					<button class="show-pass" aria-label="Показать пароль"></button>
				</label>
			</div>
			<?php wp_nonce_field( 'komandir-profile', 'komandir-profile-nonce' ); ?>
			<button type="submit" class="custom-popup-submit form-profile-save">Сохранить изменения</button>
		</form>
	</div>
</template>
