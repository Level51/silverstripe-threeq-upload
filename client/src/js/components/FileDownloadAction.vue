<template>
  <div class="threeQUpload-downloadAction">
    <a
      class="btn btn-outline-secondary"
      href=""
      @click.prevent="showDownloadModal">
      {{ $t('download.cta') }}
    </a>

    <Modal
      class="threeQUpload-downloadModal"
      v-if="isDownloadModalVisible"
      @close="isDownloadModalVisible = false">
      <h3 slot="header">
        {{ $t('download.modalTitle') }}
      </h3>
      <div slot="body">
        <div>
          <div
            class="loader"
            v-if="loading">
            <fa-icon
              icon="spinner"
              spin
            />
          </div>
          <div v-else>
            <div v-if="downloadLinks && downloadLinks.length > 0">
              <ul>
                <li
                  v-for="link in downloadLinks"
                  :key="link.key">
                  <a :href="link.url" target="_blank" rel="noopener noreferrer">
                    <span>
                      {{ link.key === 'source' ? $t('download.sourceFile') : link.key }}
                    </span>
                    <fa-icon icon="download" />
                  </a>
                </li>
              </ul>
            </div>
            <div v-else>
              {{ $t('download.emptyInfo') }}
            </div>
          </div>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script>
import axios from 'axios';
import Modal from './Modal.vue';
import { mapState } from "vuex";

export default {
  data() {
    return {
      isDownloadModalVisible: false,
      loading: false,
      downloadLinks: [],
    }
  },
  components: {
    Modal,
  },
  computed: {
    ...mapState(['file', 'payload']),
  },
  methods: {
    async showDownloadModal() {
      this.loading = true;
      this.isDownloadModalVisible = true;
      await this.fetchDownloadLinks();
      this.loading = false;
    },
    async fetchDownloadLinks() {
      const response = await axios.put(this.payload.config.getDownloadLinks, { fileId: this.file.id });

      if (response.data) {
        this.downloadLinks = Object.keys(response.data).map((key) => ({
          key,
          url: response.data[key],
        }));
      }
    }
  }
}
</script>

<style lang="less">
@import "~styles/base";
.threeQUpload .threeQUpload-downloadAction {
  .threeQUpload-downloadModal {
    .loader {
      font-size: 2rem;
    }

    ul {
      width: 100%;
      padding: 0;
      margin: 0;

      li {
        display: flex;

        &:not(:last-child) {
          border-bottom: 1px solid @color-light-grey;
        }

        a {
          display: flex;
          width: 100%;
          padding: @space-2 0;
          color: @color-mono-20;

          > :first-child {
            flex: 1 1 auto;
          }
        }
      }
    }
  }
}
</style>
