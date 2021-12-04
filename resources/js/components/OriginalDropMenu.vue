<template>
  <div
    v-if="layouts"
    class="z-20 relative"
  >
    <div
      v-if="layouts.length > 1"
      class="relative"
    >
      <div
        v-if="isLayoutsDropdownOpen"
        class="absolute rounded-lg shadow-lg max-w-full mb-3 pin-b max-h-search overflow-y-auto border border-40"
      >
        <div>
          <ul class="list-reset">
            <li
              v-for="layout in layouts"
              v-if="limitPerLayoutCounter[layout.name] === null || limitPerLayoutCounter[layout.name] > 0"
              class="border-b border-40"
            >
              <a
                :dusk="'add-' + layout.name"
                class="cursor-pointer flex items-center hover:bg-30 block py-2 px-3 no-underline font-normal bg-20"
                @click="addGroup(layout)"
              >
                <div><p class="text-90">{{ layout.title }}</p></div>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <button
      v-if="limitCounter > 0 || limitCounter === null"
      dusk="toggle-layouts-dropdown-or-add-default"
      type="button"
      tabindex="0"
      class="btn btn-default btn-primary inline-flex items-center relative"
      @click="toggleLayoutsDropdownOrAddDefault"
    >
      <span>{{ field.button }}</span>
    </button>
  </div>
</template>

<script>

export default {
  props: ['layouts', 'field', 'resourceName', 'resourceId', 'resource', 'errors', 'limitCounter', 'limitPerLayoutCounter'],

  data() {
    return {
      isLayoutsDropdownOpen: false,
    };
  },

  methods: {
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
      Nova.$emit('nova-flexible-content-add-group', layout);

      this.isLayoutsDropdownOpen = false;
    },
  },
};
</script>
