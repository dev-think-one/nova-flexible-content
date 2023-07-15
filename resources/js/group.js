import { random as randomString } from './utils/str';

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
    const data = {
      layout: this.name,
      key: this.key,
      collapsed: this.collapsed,
      attributes: {},
      files: {},
    };

    for (const item of this.values()) {
      if (item[0].indexOf('___upload-') == 0) {
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
      data.attributes[item[0]] = `___upload-${item[0]}`;
      data.files[`___upload-${item[0]}`] = item[1];
    }

    return data;
  }

  getTemporaryUniqueKey() {
    return randomString(16);
  }

  renameFields() {
    for (let i = this.fields.length - 1; i >= 0; i--) {
      this.fields[i].attribute = `${this.key}__${this.fields[i].attribute}`;
      this.fields[i].validationKey = this.fields[i].attribute;
    }
  }
}
