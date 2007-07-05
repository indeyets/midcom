<div id="main-panel" class="panel">
	<div id="main-panel-header" class="panel-header" onclick="$j('#main-panel-content').BlindToggleVertically(300, null, 'bounceout');return false;">
		<span>Toggle panel</span>
	</div>
	<div id="main-panel-content" class="panel-content" style="display: none;">
		<div id="main-panel-accordion" class="accordion">
			<div class="accordion-leaf" id="accordion-leaf-calendar">Calendars</div>
			<div class="accordion-leaf-body">
				<ul>
					<li class="first-item">
						<div class="calendar-visibility"><input type="checkbox" name="" value="" /></div>
						<div class="calendar-name" style="background-color: #FF0000;">Work</div>
						<div class="calendar-order">
							<a class="graph-arrowUp"></a>
							<a class="graph-arrowDown"></a>							</div>
						<div class="calendar-actions"><img src="<?php echo MIDCOM_STATIC_URL;?>/org.maemo.calendar/images/icons/settings.png" alt="Properties" width="16" height="16" /></div>
					</li>
					<li>
						<div class="calendar-visibility"><input type="checkbox" name="" value="" /></div>
						<div class="calendar-name" style="background-color: #0099FF;">Home</div>
						<div class="calendar-order">
							<a class="graph-arrowUp"></a>
							<a class="graph-arrowDown"></a>							</div>
						<div class="calendar-actions"><img src="<?php echo MIDCOM_STATIC_URL;?>/org.maemo.calendar/images/icons/settings.png" alt="Properties" width="16" height="16" /></div>
					</li>
					<li class="last-item">
						<div class="calendar-visibility"><input type="checkbox" name="" value="" /></div>
						<div class="calendar-name" style="background-color: #00CC99;">Jack's</div>
						<div class="calendar-order">
							<a class="graph-arrowUp"></a>
							<a class="graph-arrowDown"></a>							</div>
						<div class="calendar-actions"><img src="<?php echo MIDCOM_STATIC_URL;?>/org.maemo.calendar/images/icons/settings.png" alt="Properties" width="16" height="16" /></div>
					</li>														
				</ul>
			</div>
			<div class="accordion-leaf" id="accordion-leaf-buddies">Buddies</div>
			<div class="accordion-leaf-body">
				<p>No content</p>
			</div>
			<div class="accordion-leaf" id="accordion-leaf-agenda">Agenda</div>
			<div class="accordion-leaf-body">
				<p>No content</p>
			</div>
			<div class="accordion-leaf" id="accordion-leaf-settings">Settings</div>
			<div class="accordion-leaf-body">
				<ul>
					<li><a href="#">Global settings</a></li>
				</ul>
			</div>					
		</div>
	</div>
</div>