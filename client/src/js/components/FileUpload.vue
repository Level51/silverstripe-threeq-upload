<template>
  <div class="threeQUpload-uploader">
    <vue2-dropzone
      :options="options"
      :id="`dropzone-${payload.id}`"
      ref="dropzone"
      class="vue-dropzone"
      :include-styling="false"
      :use-custom-slot="true"
      @vdropzone-file-added="fileAdded"
      @vdropzone-success="successEvent"
      @vdropzone-error="errorEvent">
      <div class="threeQUpload-uploader-content">
        <div class="threeQUpload-uploader-content-title">
          {{ $t('upload.title') }}
        </div>
        <div class="threeQUpload-uploader-content-subTitle">
          {{ $t('upload.subTitle') }}
        </div>
      </div>
    </vue2-dropzone>
  </div>
</template>

<script>
import axios from 'axios';
import vue2Dropzone from 'vue2-dropzone';
import { mapState, mapActions } from 'vuex';

export default {
  components: { vue2Dropzone },
  data() {
    return {
      uploadUrl: null,
    };
  },
  computed: {
    ...mapState(['payload']),
    options() {
      return {
        ...this.payload.dropzoneOptions,
        url: () => this.uploadUrl,
        maxFiles: 1,
        autoProcessQueue: false,
        method: 'put',
        headers: {
          'Cache-Control': '',
          'X-Requested-With': '',
        },
        // Hook into the sending method to be able to send the raw data instead of multipart/form-data
        // Thanks to https://github.com/dropzone/dropzone/issues/590#issuecomment-51498225
        sending(file, xhr) {
          const _send = xhr.send;
          xhr.send = function () {
            _send.call(xhr, file);
          };
        },
      };
    },
  },
  methods: {
    ...mapActions(['setMessage', 'showPreview', 'setFile']),
    successEvent(file) {
      const threeQFileInfo = JSON.parse(file.xhr.response);

      // Get basic file info
      const data = {
        name: file.name,
        size: file.size,
        fileId: threeQFileInfo.FileId,
        playoutId: threeQFileInfo?.FilePlayouts[0]?.Id,
      };

      axios.post(
        this.payload.config.successCallbackEndpoint,
        data,
      ).then((response) => {
        this.setFile(response.data);
        this.showPreview();
      }).catch((err) => {
        // TODO error handling
        console.warn('server error', err);
      });
    },
    async fetchUploadUrl() {
      const file = this.$refs.dropzone.getQueuedFiles()[0];

      if (!file) {
        console.error('no file found');
        // TODO error handling
        return;
      }

      const response = await axios.post(this.payload.config.uploadUrlEndpoint, {
        name: file.name,
        type: file.type,
      });

      this.uploadUrl = response.data;
    },
    async handleUpload() {
      this.processTimeout = setTimeout(async () => {
        await this.fetchUploadUrl();
        this.$refs.dropzone.processQueue();
      }, 100);
    },
    fileAdded() {
      this.setMessage(null);
      this.handleUpload();
    },
    errorEvent(file, message, xhr) {
      this.setMessage({ type: 'error', content: message });

      if (xhr) console.log(xhr);

      this.$refs.dropzone.removeAllFiles();
      clearTimeout(this.processTimeout);
    },
  },
};
</script>

<style lang="less">
@import "~styles/base";

.threeQUpload .threeQUpload-uploader {
  margin-top: @space-2;

  .vue-dropzone {
    border: 2px dashed @color-border;
    border-radius: @border-radius;
    background: @color-mono-100;
    padding: @space-2;
    height: 68px;
    display: flex;
    justify-content: center;
    align-items: center;

    &.dz-clickable {
      cursor: pointer;
    }

    &.dz-drag-hover {
      box-shadow: 0 0 10px inset @color-border;
    }

    .threeQUpload-uploader-content {
      text-align: center;

      .threeQUpload-uploader-content-title {
        margin: 0;
        font-size: 1.15rem;
        font-weight: bold;
      }

      .threeQUpload-uploader-content-subTitle {
        font-size: 0.875rem;
      }
    }

    &.dz-started {
      .dz-message {
        display: none;
      }
    }

    .dz-preview {
      .dz-image, .dz-success-mark, .dz-error-mark {
        display: none;
      }
      .dz-details {
        display: flex;

        .dz-size {
          margin-right: @space-2;
        }
      }
      .dz-progress {
        width: 100%;
        height: 5px;
        position: relative;

        .dz-upload {
          position: absolute;
          top: 0;
          left: 0;
          background: @color-success;
          height: 100%;
        }
      }
    }
  }
}
</style>
