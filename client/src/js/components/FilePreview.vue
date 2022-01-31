<template>
  <div class="threeQUpload-preview">
    <div
      class="threeQUpload-preview-inProgress"
      v-if="!file.isFinished">
      <div class="threeQUpload-preview-inProgress-headline">
        {{ $t('inThreeQProgress.headline') }}
      </div>
      <div>
        {{ $t('inThreeQProgress.description') }}
      </div>

      <div class="mt-2">
        <a
          class="btn btn-primary"
          href=""
          @click.prevent="syncWithApi">
          <fa-icon
            v-if="isSyncing"
            icon="spinner"
            spin
          />
          <template v-if="!isSyncing">
            {{ $t('inThreeQProgress.updateCta') }}
          </template>
        </a>
      </div>
    </div>

    <div class="threeQUpload-preview-wrapper">
      <div class="threeQUpload-preview-meta">
        <div>
          <strong>{{ $t('preview.meta.title') }}</strong>
          {{ file.title }}
        </div>
        <div>
          <strong>{{ $t('preview.meta.name') }}</strong>
          {{ file.name }}
        </div>
        <div v-if="file.size && file.size.raw > 0 && file.size.formatted">
          <strong>{{ $t('preview.meta.size') }}</strong>
          {{ file.size.formatted }}
        </div>

        <div>
          <a
            class="btn btn-outline-primary"
            href=""
            @click.prevent="hidePreview">
            {{ $t('preview.changeCta') }}
          </a>

          <a
            class="btn btn-outline-danger"
            href=""
            @click.prevent="deleteFile">
            {{ $t('preview.deleteCta') }}
          </a>
        </div>
      </div>
      <div
        class="threeQUpload-preview-image"
        v-if="file.thumbnail">
        <img :src="file.thumbnail">
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { mapState, mapActions } from 'vuex';

export default {
  data() {
    return {
      isSyncing: false,
    };
  },
  computed: {
    ...mapState(['file', 'payload']),
  },
  methods: {
    ...mapActions(['hidePreview', 'deleteFile', 'setFile']),
    async syncWithApi() {
      this.isSyncing = true;
      const response = await axios.put(this.payload.config.syncWithApiEndpoint, { fileId: this.file.id });
      this.setFile(response.data);
      this.isSyncing = false;
    },
  },
};
</script>

<style lang="less">
@import "~styles/base";
.threeQUpload .threeQUpload-preview {
  border: 1px solid @color-light-grey;

  .threeQUpload-preview-wrapper {
    display: flex;
  }

  .threeQUpload-preview-image {
    width: 400px;

    img {
      display: block;
      width: 100%;
    }
  }

  .threeQUpload-preview-inProgress {
    padding: @space-2;
    border-bottom: 1px solid @color-light-grey;

    .threeQUpload-preview-inProgress-headline {
      font-weight: bold;
    }
  }

  .threeQUpload-preview-meta {
    flex: 1 1 auto;
    padding: @space-3;

    > div {
      &:not(:last-child) {
        margin-bottom: @space-2;
      }
    }

    strong {
      display: block;
    }
  }
}
</style>
