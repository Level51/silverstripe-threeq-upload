<template>
  <div class="threeQUpload">
    <div
      v-if="message"
      class="alert"
      :class="[`alert-${message.type}`]">
      {{ message.content }}
    </div>

    <template v-if="previewVisible">
      <FilePreview />
    </template>

    <template v-if="!previewVisible && canEdit">
      <template v-if="payload.config.uploadsEnabled">
        <FileUpload />

        <div class="threeQUpload-orSelectHint">
          {{ $t('generic.orSelectHint') }}
        </div>
      </template>

      <FileSelect />

      <div
        class="threeQUpload-keepFileHint"
        v-if="file && !isUploadRunning">
        <div class="threeQUpload-orSelectHint">
          {{ $t('generic.orSelectHint') }}
        </div>
        <div class="d-flex flex-column align-items-center">
          <a
            class="btn btn-light"
            href=""
            @click.prevent="showPreview">
            {{ $t('generic.cancelChange') }}

            <div class="threeQUpload-keepFileHint-filename">
              {{ file.title }}
            </div>
          </a>
        </div>
      </div>
    </template>

    <template v-if="!previewVisible && !canEdit">
      {{ $t('readonly.noFile') }}
    </template>

    <input
      type="hidden"
      :id="payload.id"
      :name="payload.name"
      :value="value">
  </div>
</template>

<script>
import axios from 'axios';
import { mapState, mapGetters, mapActions } from 'vuex';
import FileSelect from './components/FileSelect.vue';
import FileUpload from './components/FileUpload.vue';
import FilePreview from './components/FilePreview.vue';
import 'src/icons';
import * as locales from 'src/lang';

export default {
  props: {
    serverPayload: {
      type: Object,
      required: true,
    },
  },
  components: { FileSelect, FilePreview, FileUpload },
  created() {
    this.setLocale();
    this.init();
  },
  computed: {
    ...mapState(['payload', 'message', 'previewVisible', 'file', 'isUploadRunning']),
    ...mapGetters(['value', 'canEdit']),
  },
  methods: {
    ...mapActions(['initStore', 'showPreview']),
    async init() {
      await this.initStore(this.serverPayload);
    },
    setLocale() {
      const locale = this.serverPayload.lang ?? 'en';

      if (this.$i18n) {
        this.$i18n.setLocaleMessage(locale, locales[locale]);
        this.$i18n.locale = locale;
      }
    },
  },
};
</script>

<style lang="less">
@import "~styles/base";

.threeQUpload {
  .threeQUpload-orSelectHint {
    margin: @space-2 0;
    text-align: center;
    font-size: 1.25rem;
    font-weight: bold;
  }

  .threeQUpload-keepFileHint {
    .btn {
      font-size: 1.15rem;
      font-weight: bold;
    }

    .threeQUpload-keepFileHint-filename {
      font-size: 0.75rem;
      font-weight: normal;
      max-width: 300px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  }
}
</style>
