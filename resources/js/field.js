import FlexibleIndexField from './components/IndexField';
import FlexibleFormField from './components/FormField';
import FlexibleFormGroup from './components/FormGroup';
import FlexibleDeleteGroupModal from './components/DeleteGroupModal';
import FlexibleOriginalDropMenu from './components/OriginalDropMenu';
import FlexibleSearchMenu from './components/SearchMenu';
import FlexibleDetailField from './components/DetailField';
import FlexibleDetailGroup from './components/DetailGroup';
import VideoIndexField from './components/video-field/IndexField';
import VideoDetailGroup from './components/video-field/DetailField';
import VideoFormField from './components/video-field/FormField';

Nova.booting((app, store) => {
  app.component('IndexNovaFlexibleContent', FlexibleIndexField);
  app.component('FormNovaFlexibleContent', FlexibleFormField);
  app.component('FormNovaFlexibleContentGroup', FlexibleFormGroup);
  app.component('DeleteFlexibleContentGroupModal', FlexibleDeleteGroupModal);
  app.component('FlexibleDropMenu', FlexibleOriginalDropMenu);
  app.component('FlexibleSearchMenu', FlexibleSearchMenu);
  app.component('DetailNovaFlexibleContent', FlexibleDetailField);
  app.component('DetailNovaFlexibleContentGroup', FlexibleDetailGroup);

  app.component('IndexVideoField', VideoIndexField);
  app.component('DetailVideoField', VideoDetailGroup);
  app.component('FormVideoField', VideoFormField);
});
