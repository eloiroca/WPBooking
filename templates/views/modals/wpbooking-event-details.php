<div class="modal micromodal-slide" id="modal-wpbooking-event-details" aria-hidden="true">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
      <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="modal-wpbooking-event-details-title">
        <header class="modal__header">
          <h2 class="modal__title" id="modal-wpbooking-event-details-title">
            <!-- Título del evento dinámico -->
          </h2>
          <!--button class="modal__close" aria-label="Close modal" data-micromodal-close></button-->
        </header>
        <main class="modal__content" id="modal-wpbooking-event-details-content">
		    <div class="elementor-button-wrapper buttonBasica">
				<p>
					<strong><?php echo __wpb('Start'); ?>:</strong> <span id="wpbooking-event-start"></span>
				</p>
                <p>
                    <strong><?php echo __wpb('End'); ?>:</strong> <span id="wpbooking-event-end"></span>
                </p>
			</div>
        </main>
        <footer class="modal__footer">
          <button class="modal__btn" data-micromodal-close aria-label="Close this dialog window"><?php echo __wpb("Close"); ?></button>
        </footer>
      </div>
    </div>
  </div>
