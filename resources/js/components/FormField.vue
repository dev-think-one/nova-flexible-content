<template>
  <DefaultField
    :dusk="currentField.attribute"
    :field="currentField"
    :errors="errors"
    :show-help-text="showHelpText"
    :class="{'flexibleFieldFullWidth': currentField.fullWidth}"
    full-width-content
  >

    <template #field>

      <div ref="flexibleFieldContainer">
        <FormFlexibleContentGroup
          v-for="(group, index) in orderedGroups"
          :dusk="currentField.attribute + '-' + index"
          :key="group.key"
          :field="currentField"
          :group="group"
          :index="index"
          :resource-name="resourceName"
          :resource-id="resourceId"
          :errors="errors"
          :mode="mode"
          @move-up="moveUp(group.key)"
          @move-down="moveDown(group.key)"
          @remove="remove(group.key)"
        />
      </div>

      <component
        :layouts="layouts"
        :is="currentField.menu.component"
        :field="currentField"
        :limit-counter="limitCounter"
        :limit-per-layout-counter="limitPerLayoutCounter"
        :errors="errors"
        :resource-name="resourceName"
        :resource-id="resourceId"
        @addGroup="addGroup($event)"
      />

    </template>

  </DefaultField>
</template>

<script>
import Sortable from 'sortablejs'
import {DependentFormField, HandlesValidationErrors, mapProps} from 'laravel-nova';
import Group from '@/group';

export default {
  mixins: [HandlesValidationErrors, DependentFormField],

  props: {
    ...mapProps(['mode']),
  },

  components: {},

  data() {
    return {
      order: [],
      groups: {},
      files: {},
      sortableInstance: null
    };
  },

  beforeUnmount() {
    if (this.sortableInstance) {
      this.sortableInstance.destroy();
    }
  },

  computed: {
    layouts() {
      return this.currentField.layouts || false
    },

    orderedGroups() {
      return this.order.reduce((groups, key) => {
        groups.push(this.groups[key]);
        return groups;
      }, []);
    },

    limitCounter() {
      if (this.currentField.limit === null || typeof (this.currentField.limit) == "undefined") {
        return null;
      }

      return this.currentField.limit - Object.keys(this.groups).length;
    },

    limitPerLayoutCounter() {
      return this.layouts.reduce((layoutCounts, layout) => {
        if (layout.limit === null || layout.limit === 0) {
          layoutCounts[layout.name] = null;

          return layoutCounts;
        }

        let count = Object.values(this.groups).filter(group => group.name === layout.name).length;

        layoutCounts[layout.name] = layout.limit - count;

        return layoutCounts;
      }, {});
    },
  },

  methods: {
    /**
     * Set the initial, internal value for the field.
     */
    setInitialValue() {
      this.value = this.currentField.value || [];
      this.files = {};

      this.populateGroups();
      this.$nextTick(this.initSortable.bind(this));
    },

    /**
     * Update the field's internal value.
     */
    handleChange(value) {
      this.value = value || [];
      this.files = {};

      this.populateGroups();
    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) {
      let key, group;

      this.value = [];
      this.files = {};

      for (let i = 0; i < this.order.length; i++) {
        key = this.order[i];
        group = this.groups[key].serialize();

        // Attach the files for formData appending
        this.files = {...this.files, ...group.files};
        delete group.files;

        // Only serialize the group's non-file attributes
        this.value.push(group);
      }

      this.appendFieldAttribute(formData, this.currentField.attribute);
      formData.append(this.currentField.attribute, this.value.length ? JSON.stringify(this.value) : '');

      // Append file uploads
      for (let file in this.files) {
        formData.append(file, this.files[file]);
      }

      this.$nextTick(this.initSortable.bind(this));
    },

    /**
     * Register given field attribute into the parsable flexible fields register
     */
    appendFieldAttribute(formData, attribute) {
      let registered = [];

      if (formData.has('___nova_flexible_content_fields')) {
        registered = JSON.parse(formData.get('___nova_flexible_content_fields'));
      }

      registered.push(attribute);

      formData.set('___nova_flexible_content_fields', JSON.stringify(registered));
    },

    /**
     * Set the displayed layouts from the field's current value
     */
    populateGroups() {
      this.order.splice(0, this.order.length);
      this.groups = {};

      for (var i = 0; i < this.value.length; i++) {
        this.addGroup(
          this.getLayout(this.value[i].layout),
          this.value[i].attributes,
          this.value[i].key,
          this.value[i].collapsed,
        );
      }
    },

    /**
     * Retrieve layout definition from its name
     */
    getLayout(name) {
      if (!this.layouts) return;
      return this.layouts.find(layout => layout.name == name);
    },

    /**
     * Append the given layout to flexible content's list
     */
    addGroup(layout, attributes, key, collapsed) {
      if (!layout) return;

      collapsed = collapsed || false;

      const fields = attributes || JSON.parse(JSON.stringify(layout.fields));
      const group = new Group(layout.name, layout.title, fields, this.currentField, key, collapsed, layout.configs);

      this.groups[group.key] = group;
      this.order.push(group.key);
    },

    /**
     * Move group to specific index.
     */
    moveToIndex(key, newIndex) {
      if (newIndex < 0 || newIndex >= this.order.length - 1) {
        console.error(`Incorrect newIndex [${newIndex}]`);
        return;
      }

      const currentIndex = this.order.indexOf(key);

      if (currentIndex < 0 || currentIndex >= this.order.length - 1) {
        console.error(`Incorrect currentIndex [${newIndex}]`);
        return;
      }

      // Get and delete element.
      const currentElement = this.order.splice(currentIndex, 1)[0];

      // Replace element to new position.
      this.order.splice(newIndex, 0, currentElement);
    },

    /**
     * Move a group up.
     */
    moveUp(key) {
      this.moveToIndex(key, this.order.indexOf(key) - 1);
    },

    /**
     * Move a group down.
     */
    moveDown(key) {
      this.moveToIndex(key, this.order.indexOf(key) + 1);
    },

    /**
     * Remove a group.
     */
    remove(key) {
      let index = this.order.indexOf(key);

      if (index < 0) return;

      this.order.splice(index, 1);
      delete this.groups[key];
    },

    initSortable() {
      const containerRef = this.$refs['flexibleFieldContainer']

      if (!containerRef || this.sortableInstance) {
        return;
      }

      this.sortableInstance = Sortable.create(containerRef, {
        ghostClass: 'nova-flexible-content-sortable-ghost',
        dragClass: 'nova-flexible-content-sortable-drag',
        chosenClass: 'nova-flexible-content-sortable-chosen',
        direction: 'vertical',
        handle: '.nova-flexible-content-drag-button',
        scrollSpeed: 5,
        animation: 500,
        onEnd: (evt) => {
          this.moveToIndex(evt.item.id, evt.newIndex)
        }
      });
    },

  },
};
</script>
