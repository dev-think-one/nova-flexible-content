import { random as randomString } from '@/utils/str';

export default class Group {
  constructor(name, title, fields, field, key, collapsed = true, configs = {}) {
    this.name = name;
    this.title = title;
    this.fields = fields;
    this.key = key || this.getTemporaryUniqueKey(field.attribute);
    this.collapsed = collapsed;
    this.readonly = field.readonly;
    this.configs = configs;

    this.renameFields();
  }

  /**
     * Retrieve the layout's filled FormData
     */
  values() {
    const formData = new FormData();

    for (let i = 0; i < this.fields.length; i++) {
      this.fields[i].fill(formData);
    }

    return formData;
  }

  /**
     * Retrieve the layout's filled object
     */
  serialize() {
    const prefix = Nova.config('flexible-content-field.file-indicator-prefix');

    const data = {
      layout: this.name,
      key: this.key,
      collapsed: this.collapsed,
      attributes: {},
      files: {},
    };

    for (const item of this.values()) {
      if (item[0].indexOf(prefix) == 0) {
        // Previously nested file attribute
        data.files[item[0]] = item[1];
        continue;
      }

      if (!(item[1] instanceof File || item[1] instanceof Blob)) {
        // Simple input value, no need to attach files
        data.attributes[item[0]] = item[1];
        continue;
      }

      // File object, attach its file for upload
      data.attributes[item[0]] = `${prefix}${item[0]}`;
      data.files[`${prefix}${item[0]}`] = item[1];
    }

    return data;
  }

  getTemporaryUniqueKey() {
    return randomString(16);
  }

  /**
   * Assign a new unique field name to each field
   */
  renameFields() {
    const groupSeparator = Nova.config('flexible-content-field.group-separator');
    for (let i = this.fields.length - 1; i >= 0; i--) {
      this.fields[i].attribute = `${this.key}${groupSeparator}${this.fields[i].attribute}`;
      this.fields[i].validationKey = this.fields[i].attribute;

      if (this.fields[i].dependsOn) {
        Object.keys(this.fields[i].dependsOn).forEach((key) => {
          this.fields[i].dependsOn[`${this.key}${groupSeparator}${key}`] = this.fields[i].dependsOn[key];
          delete this.fields[i].dependsOn[key];
        });
      }
    }
  }
}
