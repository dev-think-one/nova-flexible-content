<template>
  <div
    v-if="layouts"
    class="w-3/5"
  >
    <div v-if="limitCounter > 0 || limitCounter === null">
      <div v-if="layouts.length === 1">
        <default-button
          dusk="toggle-layouts-dropdown-or-add-default"
          type="button"
          tabindex="0"
          @click="toggleLayoutsDropdownOrAddDefault"
        >
          <span>{{ field.button }}</span>
        </default-button>
      </div>
      <div v-if="layouts.length > 1">
        <div style="min-width: 300px;">
          <div class="flexible-search-menu-multiselect">
            <Multiselect
              v-bind="attributes"
              ref="select"
              v-model="selectedLayout"
              :options="availableLayouts"
              :placeholder="field.button"
              track-by="name"
              :show-options="true"
              :searchable="true"
              @change="selectLayout"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Multiselect from '@vueform/multiselect';

export default {
  components: {
    Multiselect,
  },
  props: ['layouts', 'field', 'resourceName', 'resourceId', 'resource', 'errors', 'limitCounter', 'limitPerLayoutCounter'],
  emits: ['addGroup'],
  data() {
    return {
      selectedLayout: null,
      isLayoutsDropdownOpen: false,
    };
  },
  computed: {
    attributes() {
      return {
        selectLabel: this.field.menu.data.selectLabel || this.__('Press enter to select'),
        label: this.field.menu.data.label || 'title',
        openDirection: this.field.menu.data.openDirection || 'bottom',
      };
    },
    availableLayouts() {
      return this.layouts.filter((layout) => this.limitPerLayoutCounter[layout.name] === null || this.limitPerLayoutCounter[layout.name] > 0).reduce((carry, layout) => {
        carry[layout.name] = layout.title;
        return carry;
      }, {});
    },
  },
  methods: {
    selectLayout(layoutName) {
      const layout = this.layouts.find((layout) => layout.name === layoutName);
      this.addGroup(layout);
    },
    /**
     * Display or hide the layouts choice dropdown if there are multiple layouts
     * or directly add the only available layout.
     */
    toggleLayoutsDropdownOrAddDefault(event) {
      if (this.layouts.length === 1) {
        return this.addGroup(this.layouts[0]);
      }
      this.isLayoutsDropdownOpen = !this.isLayoutsDropdownOpen;
    },
    /**
     * Append the given layout to flexible content's list
     */
    addGroup(layout) {
      if (!layout) return;
      this.$emit('addGroup', layout);
      this.isLayoutsDropdownOpen = false;
      setTimeout(() => {
        this.$refs.select.clear();
        this.selectedLayout = null;
      }, 100);
    },
  },
};
</script>

<style src="@vueform/multiselect/themes/default.css"></style>
