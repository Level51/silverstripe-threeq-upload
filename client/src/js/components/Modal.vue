<template>
  <transition name="modal">
    <div class="modal-mask threeQUpload-modal">
      <div class="modal-wrapper">
        <div class="modal-container">
          <div class="modal-header">
            <slot name="header" />
          </div>

          <div class="modal-body">
            <slot name="body" />
          </div>

          <div class="modal-footer">
            <slot name="footer">
              <button
                class="btn btn-outline-secondary"
                @click="$emit('close')">
                OK
              </button>
            </slot>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  mounted() {
    window.addEventListener('keyup', this.handleKeyup, false);
  },
  beforeDestroy() {
    window.removeEventListener('keyup', this.handleKeyup, false);
  },
  methods: {
    handleKeyup(e) {
      if (e.target.nodeName === 'INPUT') return;
      if (e.keyCode === 27) { this.$emit('close'); }
    }
  }
};
</script>

<style lang="less">
@import (reference) "../../styles/base";

.threeQUpload-modal {
  &.modal-mask {
    position: fixed;
    z-index: 9998;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, .5);
    display: flex;
    justify-content: center;
    align-items: center;
    transition: opacity .3s ease;
  }

  .modal-wrapper {
    width: 400px;
    max-width: 90%;
    overflow: hidden;
    background-color: #fff;
    border-radius: 2px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
    transition: all .3s ease;
  }

  .modal-container {
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    overflow: hidden;

    .modal-header {
      flex: none
    }

    .modal-body {
      flex: 1 1 auto;
      min-width: 0;
      min-height: 0;
      overflow: auto;
    }

    .modal-footer {
      flex: none;
      padding: 20px;
    }
  }

  .modal-header h3 {
    margin: @space-2 0 !important;
    font-weight: bold;
  }

  &.modal-enter {
    opacity: 0;
  }

  &.modal-leave-active {
    opacity: 0;
  }

  &.modal-enter .modal-wrapper,
  &.modal-leave-active .modal-wrapper {
    -webkit-transform: scale(1.1);
    transform: scale(1.1);
  }
}
</style>
