<template>
  <component
    :is="field.fullWidth ? 'full-width-field' : 'default-field'"
    :dusk="field.attribute"
    :field="field"
    :errors="errors"
    full-width-content
    :show-help-text="showHelpText"
  >
    <template slot="field">
      <div
        v-if="order.length > 0"
      >
        <form-nova-flexible-content-group
          v-for="(group, index) in orderedGroups"
          :key="group.key"
          :dusk="field.attribute + '-' + index"
          :field="field"
          :group="group"
          :index="index"
          :resource-name="resourceName"
          :resource-id="resourceId"
          :resource="resource"
          :errors="errors"
          @move-up="moveUp(group.key)"
          @move-down="moveDown(group.key)"
          @remove="remove(group.key)"
        />
      </div>

      <component
        :is="field.menu.component"
        :layouts="layouts"
        :field="field"
        :allow-add-group="allowAddGroup"
        :allow-add-groups-map="allowAddGroupsMap"
        :errors="errors"
        :resource-name="resourceName"
        :resource-id="resourceId"
        :resource="resource"
        @addGroup="addGroup($event)"
      />
    </template>
  </component>
</template>

<script>

import { FormField, HandlesValidationErrors } from 'laravel-nova';
import FullWidthField from './FullWidthField';
import Group from '../group';

export default {
  components: { FullWidthField },
  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'resource', 'field'],

  data() {
    return {
      order: [],
      groups: {},
      files: {},
    };
  },

  computed: {
    layouts() {
      return this.field.layouts || [];
    },
    orderedGroups() {
      return this.order.reduce((groups, key) => {
        groups.push(this.groups[key]);
        return groups;
      }, []);
    },

    limitPerField() {
      return parseInt(this.field.limit) || 0;
    },

    allowAddGroup() {
      if (this.limitPerField > 0
        && (this.limitPerField - Object.keys(this.groups).length) <= 0
      ) {
        return false;
      }

      return Object.values(this.allowAddGroupsMap).some((isAllow) => isAllow);
    },

    allowAddGroupsMap() {
      return this.layouts.reduce((result, layout) => {
        const limit = parseInt(layout.limit, 10);
        if (limit > 0) {
          result[layout.name] = limit > Object.values(this.groups)
            .filter((group) => group.name === layout.name).length;
        } else {
          result[layout.name] = true;
        }

        return result;
      }, {});
    },
  },

  methods: {
    /*
         * Set the initial, internal value for the field.
         */
    setInitialValue() {
      this.value = this.field.value || [];
      this.files = {};
      this.populateGroups();
    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) {
      let key;
      let
        group;

      this.value = [];
      this.files = {};

      for (let i = 0; i < this.order.length; i++) {
        key = this.order[i];
        group = this.groups[key].serialize();

        // Attach the files for formData appending
        this.files = { ...this.files, ...group.files };
        delete group.files;

        // Only serialize the group's non-file attributes
        this.value.push(group);
      }

      this.appendFieldAttribute(formData, this.field.attribute);
      formData.append(this.field.attribute, this.value.length ? JSON.stringify(this.value) : '');

      // Append file uploads
      for (const file in this.files) {
        formData.append(file, this.files[file]);
      }
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
     * Update the field's internal value.
     */
    handleChange(value) {
      this.value = value || [];
      this.files = {};

      this.populateGroups();
    },

    /**
     * Set the displayed layouts from the field's current value
     */
    populateGroups() {
      this.order.splice(0, this.order.length);
      this.groups = {};

      for (let i = 0; i < this.value.length; i++) {
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
      return this.layouts.find((layout) => layout.name == name);
    },

    /**
     * Append the given layout to flexible content's list
     */
    addGroup(layout, attributes, key, collapsed) {
      if (!layout) return;

      collapsed = collapsed || false;

      const fields = attributes || JSON.parse(JSON.stringify(layout.fields));
      const group = new Group(layout.name, layout.title, fields, this.field, key, collapsed);

      this.$set(this.groups, group.key, group);
      this.order.push(group.key);
    },

    /**
     * Move a group up
     */
    moveUp(key) {
      const index = this.order.indexOf(key);

      if (index <= 0) return;

      this.order.splice(index - 1, 0, this.order.splice(index, 1)[0]);
    },

    /**
     * Move a group down
     */
    moveDown(key) {
      const index = this.order.indexOf(key);

      if (index < 0 || index >= this.order.length - 1) return;

      this.order.splice(index + 1, 0, this.order.splice(index, 1)[0]);
    },

    /**
     * Remove a group
     */
    remove(key) {
      const index = this.order.indexOf(key);

      if (index < 0) return;

      this.order.splice(index, 1);
      this.$delete(this.groups, key);
    },
  },
};
</script>
