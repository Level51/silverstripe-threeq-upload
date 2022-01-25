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

    <template v-if="!previewVisible">
      <FileUpload />

      <div class="threeQUpload-orSelectHint">
        {{ $t('generic.orSelectHint') }}
      </div>

      <FileSelect />

      <div v-if="file" class="mt-4">
        <a
          class="btn btn-primary"
          href=""
          @click.prevent="showPreview" >
          {{ $t('generic.cancel') }}
        </a>
      </div>
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
    ...mapState(['payload', 'message', 'previewVisible', 'file']),
    ...mapGetters(['value']),
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

.threeQUpload-orSelectHint {
  margin: @space-2 0;
  text-align: center;
  font-size: 1.25rem;
  font-weight: bold;
}
</style>
