<?
$MESS['skyweb24.popuppro_EVENT_NAME_ROULETTE']='Отправка результата рулетки';
$MESS['skyweb24.popuppro_EVENT_NAME_DISCOUNT']='Отправка дисконтной карты';
$MESS['skyweb24.popuppro_EVENT_DESCRIPTION_ROULETTE']='#EMAIL# - Email
#RESULT_TEXT# - Результат рулетки
#COUPON# - Купон';
$MESS['skyweb24.popuppro_EVENT_DESCRIPTION_DISCOUNT']='#EMAIL# - Email
#NAME# - Имя пользователя
#LAST_NAME# - Фамилия пользователя
#COUPON# - Купон';
$MESS['skyweb24.popuppro_TEMPLATE_SUBJECT']='Ваш купон на сайте #SITE_NAME#';
$MESS['skyweb24.popuppro_TEMPLATE_SUBJECT_ROULETTE']='Поздравляем, вы выиграли в рулетку на сайте #SITE_NAME#!';
$MESS['skyweb24.popuppro_TEMPLATE_SUBJECT_DISCOUNT']='Ваша дисконтная карта на сайте #SITE_NAME#!';
$MESS['skyweb24.popuppro_TEMPLATE_MESSAGE']='
<style type="text/css">
.bx-mailpost{font-family: Arial, "Helvetica Neue", Helvetica, sans-serif; font-size: 14px;}
</style>
<table class="bx-mailpost" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
	<td align="center">
		<table cellpadding="0" cellspacing="0" style="border-collapse: collapse; max-width: 600px; background: #FFFFFF;">
		<tbody>
		<tr>
			<td style="padding: 0px 10px; border: 2px dashed #3498db;" align="middle">
				<table cellpadding="0">
				<tbody>
				<tr>
					<td style="padding: 5px; vertical-align: top;">
						<h2 style="line-height: 1.5em;">Наш сайт #SITE_NAME#, дарит вам подарочный купон!</h2>
						<p style="text-align: center;">
 <img width="128" src="/bitrix/components/skyweb24/popup.pro/templates/coupon_type1/img/2.png" height="128">
						</p>
						<p style="text-align: center; margin: 30px 0;">
 <span style="color: #3498db; border: 2px solid #3498db; border-radius: 5px; padding: 15px 20px; text-transform: uppercase; text-decoration: none; font-size: 18px;">#COUPON#</span>
						</p>
					</td>
				</tr>
				</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<p style="color: #95a5a6;">
					Нужна помощь? Свяжитесь с нами <a href="mailto:#DEFAULT_EMAIL_FROM#" style="color: #3498db;">#DEFAULT_EMAIL_FROM#</a>
				</p>
			</td>
		</tr>
		</tbody>
		</table>
	</td>
</tr>
</tbody>
</table>';
$MESS['skyweb24.popuppro_TEMPLATE_MESSAGE_ROULETTE']='
<style type="text/css">
.bx-mailpost{font-family: Arial, "Helvetica Neue", Helvetica, sans-serif; font-size: 14px;}
</style>
<table class="bx-mailpost" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
	<td align="center">
		<table cellpadding="0" cellspacing="0" style="border-collapse: collapse; max-width: 600px; background: #FFFFFF;">
		<tbody>
		<tr>
			<td style="padding: 0px 10px; border: 2px dashed #3498db;" align="middle">
				<table cellpadding="0">
				<tbody>
				<tr>
					<td style="padding: 5px; vertical-align: top;">
						<h2 style="line-height: 1.5em;">Поздравляем, Вы выиграли в рулетку!</h2>
						<p style="text-align: center;">
 <img width="128" src="/bitrix/components/skyweb24/popup.pro/templates/coupon_type1/img/2.png" height="128">
						</p>
						<p style="text-align:center;margin:30px 0;">
							Ваш подарок - «#RESULT_TEXT#»!
						</p>
						<p style="text-align: center; margin: 30px 0;">
 <span style="color: #3498db; border: 2px solid #3498db; border-radius: 5px; padding: 15px 20px; text-transform: uppercase; text-decoration: none; font-size: 18px;">#COUPON#</span>
						</p>
					</td>
				</tr>
				</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<p style="color: #95a5a6;">
					 Нужна помощь? Свяжитесь с нами <a href="mailto:#DEFAULT_EMAIL_FROM#" style="color: #3498db;">#DEFAULT_EMAIL_FROM#</a>
				</p>
			</td>
		</tr>
		</tbody>
		</table>
	</td>
</tr>
</tbody>
</table>';
$MESS['skyweb24.popuppro_TEMPLATE_MESSAGE_DISCOUNT']='<style type="text/css">
.bx-mailpost{font-family: Arial, "Helvetica Neue", Helvetica, sans-serif; font-size: 14px;}
</style>
<table class="bx-mailpost" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
	<td align="center">
		<table cellpadding="0" cellspacing="0" style="border-collapse: collapse; max-width: 600px; background: #FFFFFF;">
		<tbody>
		<tr>
			<td style="padding: 0px 10px; border: 2px dashed #3498db;" align="middle">
				<table cellpadding="0">
				<tbody>
				<tr>
					<td style="padding: 5px; vertical-align: top;">
						<h2 style="line-height: 1.5em; text-align:center">Уважаемый #NAME# #LASTNAME#!<br>
						 Для вас была создана дисконтная карта!</h2>
						<p style="text-align: center; margin: 30px 0;">
 <span style="color: #3498db; border: 2px solid #3498db; border-radius: 5px; padding: 15px 20px; text-transform: uppercase; text-decoration: none; font-size: 18px;">#COUPON#</span>
						</p>
					</td>
				</tr>
				</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<p style="color: #95a5a6;">
					 Нужна помощь? Свяжитесь с нами <a href="mailto:#DEFAULT_EMAIL_FROM#" style="color: #3498db;">#DEFAULT_EMAIL_FROM#</a>
				</p>
			</td>
		</tr>
		</tbody>
		</table>
	</td>
</tr>
</tbody>
</table>';