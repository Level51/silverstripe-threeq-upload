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
        spellcheck="false">
    </vue-simple-suggest>
  </div>
</template>

<script>
import VueSimpleSuggest from 'vue-simple-suggest/dist/cjs';
import axios from 'axios';
import { mapState } from 'vuex';

export default {
  data() {
    return {
      term: '',
      selection: null,
    };
  },
  components: { VueSimpleSuggest },
  computed: {
    ...mapState(['payload', 'file']),
    cleanTerm() {
      return this.term && typeof this.term === 'string' ? this.term.trim() : '';
    },
    endpoint() {
      return this.payload.config.searchEndpoint;
    },
  },
  created() {
    if (this.file) {
      this.term = this.file.title;
    }
  },
  methods: {
    selected(suggestion) {
      this.$store.dispatch('setFile', suggestion);
    },
    suggest() {
      return new Promise((resolve) => {
        if (this.cleanTerm.length < this.payload.config.minSearchChars) resolve([]);
        else {
          axios
            .get(this.endpoint)
            .then((response) => {
              resolve(response.data);
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
