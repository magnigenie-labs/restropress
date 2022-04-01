<style type="text/css">
/**************************\
  Basic Modal Styles
\**************************/

.modal__overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.6);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal__container {
  background-color: #fff;
  padding: 0px;
  width: 600px;
  max-height: 100vh;
  border-radius: 4px;
  overflow-y: auto;
  box-sizing: border-box;
}

.modal__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 15px;
}

.modal__title {
  margin-top: 0;
  margin-bottom: 0;
  font-weight: 600;
  font-size: 1.25rem;
  line-height: 1.25;
  box-sizing: border-box;
}

.modal__close {
  background: #383838;
  border: 0;
  padding: 0px;
  width: 22px;
  height: 22px;
}

.modal__header .modal__close:before {
  content: "\2715";
  left: 1px;
  position: relative;
}

.modal__content {
  line-height: 1.5;
  color: rgba(0,0,0,.8);
}

.modal__btn {
  font-size: .875rem;
  padding-left: 1rem;
  padding-right: 1rem;
  padding-top: .5rem;
  padding-bottom: .5rem;
  background-color: #e6e6e6;
  color: rgba(0,0,0,.8);
  border-radius: .25rem;
  border-style: none;
  border-width: 0;
  cursor: pointer;
  -webkit-appearance: button;
  text-transform: none;
  overflow: visible;
  line-height: 1.15;
  margin: 0;
  will-change: transform;
  -moz-osx-font-smoothing: grayscale;
  -webkit-backface-visibility: hidden;
  backface-visibility: hidden;
  -webkit-transform: translateZ(0);
  transform: translateZ(0);
  transition: -webkit-transform .25s ease-out;
  transition: transform .25s ease-out;
  transition: transform .25s ease-out,-webkit-transform .25s ease-out;
}

.modal__btn:focus, .modal__btn:hover {
  -webkit-transform: scale(1.05);
  transform: scale(1.05);
}

.modal__btn-primary {
  background-color: #00449e;
  color: #fff;
}

/**************************\
  Demo Animation Style
\**************************/
@keyframes mmfadeIn {
    from { opacity: 0; }
      to { opacity: 1; }
}

@keyframes mmfadeOut {
    from { opacity: 1; }
      to { opacity: 0; }
}

@keyframes mmslideIn {
  from { transform: translateY(15%); }
    to { transform: translateY(0); }
}

@keyframes mmslideOut {
    from { transform: translateY(0); }
    to { transform: translateY(-10%); }
}

.micromodal-slide {
  display: none;
}

.micromodal-slide.is-open {
  display: block;
}

.micromodal-slide[aria-hidden="false"] .modal__overlay {
  animation: mmfadeIn .3s cubic-bezier(0.0, 0.0, 0.2, 1);
}

.micromodal-slide[aria-hidden="false"] .modal__container {
  animation: mmslideIn .3s cubic-bezier(0, 0, .2, 1);
}

.micromodal-slide[aria-hidden="true"] .modal__overlay {
  animation: mmfadeOut .3s cubic-bezier(0.0, 0.0, 0.2, 1);
}

.micromodal-slide[aria-hidden="true"] .modal__container {
  animation: mmslideOut .3s cubic-bezier(0, 0, .2, 1);
}

.micromodal-slide .modal__container,
.micromodal-slide .modal__overlay {
  will-change: transform;
}

/* Special Conditions */
.show-service-options .modal__container {
  width: 415px;
}
.loading .modal__container{
  width: 350px;
  text-align: center;
}
</style>

<div class="modal micromodal-slide" id="rpressModal" aria-hidden="true">
  <div class="modal__overlay" tabindex="-1" data-micromodal-close>
    <div class="modal__container modal-content" role="dialog" aria-modal="true">
      <header class="modal__header modal-header">
        <h2 class="modal__title modal-title"></h2>
        <button class="modal__close" aria-label="Close modal" data-micromodal-close></button>
      </header>
      <main class="modal__content modal-body">
      </main>
      <footer class="modal__footer modal-footer">
        <div class="rpress-popup-actions edit-rpress-popup-actions rp-col-md-12">
          <div class="rp-col-md-4 rp-col-xs-4 btn-count">
            <div class="rp-col-md-4 rp-col-sm-4 rp-col-xs-4">
              <input type="button" value="&#8722;" class="qtyminus qtyminus-style qtyminus-style-edit">
            </div>
            <div class="rp-col-md-4 rp-col-sm-4 rp-col-xs-4">
              <input type="text" name="quantity" value="1" class="qty qty-style" readonly>
            </div>
            <div class="rp-col-md-4 rp-col-sm-4 rp-col-xs-4">
              <input type="button" value="&#43;" class="qtyplus qtyplus-style qtyplus-style-edit">
            </div>
          </div>
          <div class="rp-col-md-8 rp-col-xs-8">
            <a href="javascript:void(0);" data-title="" data-item-qty="1" data-cart-key="" data-item-id="" data-variable-id="" data-item-price="" data-cart-action="" class="center submit-fooditem-button text-center inline rp-col-md-6">
              <span class="cart-action-text rp-ajax-toggle-text"></span>
              <span class="cart-item-price"></span>
            </a>
          </div>
        </div>
      </footer>
    </div>
  </div>
</div>
