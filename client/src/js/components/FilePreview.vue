<template>
  <div class="threeQUpload-preview">
    <div class="threeQUpload-preview-meta">
      <div>
        <strong>{{ $t('preview.meta.title') }}</strong>
        {{ file.title }}
      </div>
      <div>
        <strong>{{ $t('preview.meta.name') }}</strong>
        {{ file.name }}
      </div>
      <div v-if="file.size && file.size.formatted">
        <strong>{{ $t('preview.meta.size') }}</strong>
        {{ file.size.formatted }}
      </div>

      <div>
        <a
          class="btn btn-primary"
          href=""
          @click.prevent="hidePreview" >
          {{ $t('preview.changeCta') }}
        </a>

        <a
          class="btn btn-danger"
          href=""
          @click.prevent="deleteFile" >
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
</template>

<script>
import { mapState, mapActions } from 'vuex';

export default {
  computed: {
    ...mapState(['file']),
  },
  methods: {
    ...mapActions(['hidePreview', 'deleteFile']),
  }
};
</script>

<style lang="less">
@import "~styles/base";
.threeQUpload .threeQUpload-preview {
  border: 1px solid @color-light-grey;
  display: flex;

  .threeQUpload-preview-image {
    width: 400px;

    img {
      display: block;
      width: 100%;
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
