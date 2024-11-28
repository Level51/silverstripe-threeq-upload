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
                  <div class="title">
                    {{ link.key === 'source' ? $t('download.sourceFile') : link.key }}
                  </div>

                  <div class="actions">
                    <a
                      :href="link.url"
                      target="_blank"
                      rel="noopener noreferrer"
                      :title="$t('download.downloadTooltip')">
                      <fa-icon icon="download" />
                    </a>

                    <UseClipboard
                      v-slot="{ copy, copied }"
                      :source="link.url">
                      <a
                        :href="link.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        :title="$t('download.clipboardTooltip')"
                        @click.prevent="copy()">
                        <fa-icon
                          :icon="copied ? 'check' : 'copy'" />
                      </a>
                    </UseClipboard>
                  </div>
                </li>
              </ul>
            </div>
            <div v-else>
              {{ $t('download.emptyInfo') }}
            </div>
          </div>
        </div>
      </div>
      <template slot="footer">
        <button
          class="btn btn-outline-secondary"
          @click="isDownloadModalVisible = false">
          {{ $t('download.closeModal') }}
        </button>
      </template>
    </Modal>
  </div>
</template>

<script>
import axios from 'axios';
import { mapState } from 'vuex';
import { UseClipboard } from '@vueuse/components';
import Modal from './Modal.vue';

export default {
  data() {
    return {
      isDownloadModalVisible: false,
      loading: false,
      downloadLinks: [],
    };
  },
  components: {
    Modal,
    UseClipboard,
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
    },
  },
};
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
        align-items: center;
        padding: @space-2 @space-1;

        &:not(:last-child) {
          border-bottom: 1px solid @color-light-grey;
        }

        div.title {
          flex: 1 1 auto;
        }

        div.actions {
          --action-size: 25px;
          flex: none;
          display: flex;
          gap: @space-2;

          a {
            color: @color-mono-20;
            transition: color 200ms ease-in-out;
            width: var(--action-size);
            height: var(--action-size);
            line-height: var(--action-size);
            border: 1px solid;
            text-align: center;
            border-radius: 4px;

            &:hover {
              color: @color-blue-primary;
            }
          }
        }
      }
    }
  }
}
</style>
