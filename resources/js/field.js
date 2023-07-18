import FlexibleIndexField from '@/components/IndexField';
import FlexibleFormField from '@/components/FormField';
import FlexibleFormGroup from '@/components/FormGroup';
import FlexibleDetailField from '@/components/DetailField';
import FlexibleDetailGroup from '@/components/DetailGroup';

/* LayoutMenus */
import FlexibleDefaultMenu from '@/components/LayoutMenu/Default';
import FlexibleSearchableMenu from '@/components/LayoutMenu/Searchable';

Nova.booting((app, store) => {
  app.component('IndexFlexibleContent', FlexibleIndexField);
  app.component('FormFlexibleContent', FlexibleFormField);
  app.component('FormFlexibleContentGroup', FlexibleFormGroup);
  app.component('DetailFlexibleContent', FlexibleDetailField);
  app.component('DetailFlexibleContentGroup', FlexibleDetailGroup);

  /* LayoutMenus */
  app.component('FlexibleDefaultMenu', FlexibleDefaultMenu);
  app.component('FlexibleSearchableMenu', FlexibleSearchableMenu);
});
