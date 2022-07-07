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
        class="z-20 absolute rounded-lg shadow-lg max-w-full top-full mt-3 pin-b max-h-search overflow-y-auto border border-gray-100 dark:border-gray-700"
      >
        <div>
          <ul class="list-reset">
            <li
              v-for="layout in layouts"
              v-if="!allowAddGroupsMap.hasOwnProperty(layout.name) || allowAddGroupsMap[layout.name]"
              class="border-b border-40"
            >
              <a
                :dusk="'add-' + layout.name"
                class="cursor-pointer flex items-center hover:bg-gray-50 dark:hover:bg-gray-900 block py-2 px-3 no-underline font-normal bg-white dark:bg-gray-800"
                @click="addGroup(layout)"
              >
                <div><p class="text-90">{{ layout.title }}</p></div>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <default-button
      v-if="allowAddGroup"
      dusk="toggle-layouts-dropdown-or-add-default"
      type="button"
      tabindex="0"
      @click="toggleLayoutsDropdownOrAddDefault"
    >
      <span>{{ field.button }}</span>
    </default-button>
  </div>
</template>

<script>

export default {
  props: ['layouts', 'field', 'resourceName', 'resourceId', 'resource', 'errors', 'allowAddGroup', 'allowAddGroupsMap'],

  data() {
    return {
      isLayoutsDropdownOpen: false,
    };
  },

  methods: {
    toggleLayoutsDropdownOrAddDefault() {
      if (this.layouts.length === 1) {
        return this.addGroup(this.layouts[0]);
      }

      this.isLayoutsDropdownOpen = !this.isLayoutsDropdownOpen;
    },
    addGroup(layout) {
      if (!layout) return;

      this.$emit('addGroup', layout);
      Nova.$emit('nova-flexible-content-add-group', layout);

      this.isLayoutsDropdownOpen = false;
    },
  },
};
</script>
