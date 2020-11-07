Nova.booting((Vue, router, store) => {
  Vue.component('index-input-select', require('./components/IndexField'))
  Vue.component('detail-input-select', require('./components/DetailField'))
  Vue.component('form-input-select', require('./components/FormField'))
})
