<template>
  <PanelItem
    :field="field"
    :class="{'flexibleDetailFieldFullWidth': field.fullWidth}"
  >
    <template #value>
      <DetailFlexibleContentGroup
        v-for="(group, index) in groups"
        :index="index"
        :dusk="`detail-${field.attribute}-${index}`"
        :group="group"
        :resource-name="resourceName"
        :resource-id="resourceId"
        :attribute="field.attribute"
      />
    </template>
  </PanelItem>
</template>

<script>
import Group from '@/group';

export default {

  props: ['resource', 'resourceName', 'resourceId', 'field'],

  data() {
    return {
      groups: {},
    };
  },

  mounted() {
    this.prefillGroups();
  },

  methods: {
    /**
     * Retrieve layout definition from its name
     */
    prefillGroups() {
      this.groups = this.field.value.reduce((groups, item) => {
        const layout = this.getLayout(item.layout);
        if (!layout) {
          return groups;
        }

        const group = new Group(
          layout.name,
          layout.title,
          item.attributes,
          this.field,
          item.key,
          item.collapsed,
          layout.configs,
        );

        if (!group) {
          return groups;
        }

        groups.push(group);

        return groups;
      }, []);
    },

    /**
     * Retrieve layout definition from its name
     */
    getLayout(name) {
      if (!this.field.layouts) {
        return null;
      }

      return this.field.layouts.find((layout) => layout.name === name);
    },
  },
};
</script>
