Nova.booting((Vue, router, store) => {
  // Vue.component('index-nova-flexible-content', require('./components/IndexField').default)
  Vue.component('DetailNovaFlexibleContent', require('./components/DetailField.vue').default);
  Vue.component('DetailNovaFlexibleContentGroup', require('./components/DetailGroup.vue').default);
  Vue.component('FormNovaFlexibleContent', require('./components/FormField.vue').default);
  Vue.component('FormNovaFlexibleContentGroup', require('./components/FormGroup.vue').default);
  Vue.component('FlexibleDropMenu', require('./components/OriginalDropMenu.vue').default);
  Vue.component('FlexibleSearchMenu', require('./components/SearchMenu.vue').default);
  Vue.component('DeleteFlexibleContentGroupModal', require('./components/DeleteGroupModal.vue').default);
  Vue.component('IconArrowDown', require('./components/icons/ArrowDown.vue').default);
  Vue.component('IconArrowUp', require('./components/icons/ArrowUp.vue').default);
  Vue.component('IconPlusSquare', require('./components/icons/PlusSquare.vue').default);
  Vue.component('IconMinusSquare', require('./components/icons/MinusSquare.vue').default);
});
