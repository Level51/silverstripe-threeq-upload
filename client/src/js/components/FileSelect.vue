<template>
  <div class="threeQUpload-select">
    <vue-simple-suggest
      v-model="term"
      :list="suggest"
      display-attribute="title"
      value-attribute="id"
      :debounce="400"
      :destyled="false"
      @select="selected"
      ref="suggestField"
      :prevent-submit="false"
      :min-length="payload.config.minSearchChars">
      <input
        :placeholder="$t('search.placeholder')"
        type="text"
        name="term"
        :value="term"
        autocomplete="off"
        autocorrect="off"
        autocapitalize="off"
        spellcheck="false"
        :disabled="isUploadRunning">
    </vue-simple-suggest>

    <div
      v-if="isLoading"
      class="threeQUpload-select-loadingIndicator">
      <fa-icon
        icon="spinner"
        spin />
    </div>
  </div>
</template>

<script>
import VueSimpleSuggest from 'vue-simple-suggest/dist/cjs';
import axios from 'axios';
import { mapState, mapActions } from 'vuex';

export default {
  data() {
    return {
      term: '',
      selection: null,
      isLoading: false,
    };
  },
  components: { VueSimpleSuggest },
  computed: {
    ...mapState(['payload', 'file', 'isUploadRunning']),
    cleanTerm() {
      return this.term && typeof this.term === 'string' ? this.term.trim() : '';
    },
    endpoint() {
      return this.payload.config.searchEndpoint;
    },
  },
  methods: {
    ...mapActions(['setFile', 'showPreview']),
    async selected(suggestion) {
      this.isLoading = true;
      const response = await axios.post(
        this.payload.config.selectFileEndpoint,
        {
          fileId: suggestion.id,
        },
      );

      this.setFile(response.data);
      this.isLoading = false;
      this.showPreview();
    },
    suggest() {
      this.isLoading = true;
      return new Promise((resolve) => {
        if (this.cleanTerm.length < this.payload.config.minSearchChars) resolve([]);
        else {
          axios
            .get(this.endpoint)
            .then((response) => {
              resolve(response.data);
              this.isLoading = false;
            });
        }
      });
    },
  },
};
</script>

<style lang="less">
@import "~styles/base";
.threeQUpload .threeQUpload-select {
  position: relative;

  .threeQUpload-select-loadingIndicator {
    position: absolute;
    top: 0;
    right: 0;
    font-size: 1.25rem;
    height: 40px;
    width: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  input[type=text] {
    display: block;
    width: 100%;
    height: 40px;
    padding: @space-2;
    border: 1px solid @color-light-grey;
    background: @color-mono-100;
    transition: all 250ms ease-in-out;
    outline: none;
    box-shadow: none;
    -webkit-appearance: none;
    border-radius: @border-radius;
    text-align: center;

    &:focus {
      border-color: @color-highlight;
    }
  }

  .vue-simple-suggest {
    position: relative;

    > ul {
      list-style: none;
      padding-left: 0;
      margin: 0;
    }

    .suggestions {
      position: absolute;
      width: 100%;
      left: 0;
      top: 100%;
      background: fade(@color-mono-100, 93);
      z-index: 1000;
      box-shadow: 0 3px 5px rgba(0, 0, 0, .16);
      border: 1px solid @color-highlight;
      border-top: 0;
      border-bottom-left-radius: @border-radius;
      border-bottom-right-radius: @border-radius;

      .suggest-item {
        cursor: pointer;
        .noselect();

        &.hover, &.selected {
          background-color: @color-highlight;
          color: @color-mono-100;
        }
      }

      .suggest-item, .misc-item {
        padding: @space-2;
      }
    }
  }
}
</style>
