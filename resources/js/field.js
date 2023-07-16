import FlexibleIndexField from './components/IndexField';
import FlexibleFormField from './components/FormField';
import FlexibleFormGroup from './components/FormGroup';
import FlexibleDeleteGroupModal from './components/DeleteGroupModal';
import FlexibleOriginalDropMenu from './components/OriginalDropMenu';
import FlexibleSearchMenu from './components/SearchMenu';
import FlexibleDetailField from './components/DetailField';
import FlexibleDetailGroup from './components/DetailGroup';

Nova.booting((app, store) => {
  app.component('IndexNovaFlexibleContent', FlexibleIndexField);
  app.component('FormNovaFlexibleContent', FlexibleFormField);
  app.component('FormNovaFlexibleContentGroup', FlexibleFormGroup);
  app.component('DeleteFlexibleContentGroupModal', FlexibleDeleteGroupModal);
  app.component('FlexibleDropMenu', FlexibleOriginalDropMenu);
  app.component('FlexibleSearchMenu', FlexibleSearchMenu);
  app.component('DetailNovaFlexibleContent', FlexibleDetailField);
  app.component('DetailNovaFlexibleContentGroup', FlexibleDetailGroup);
});
