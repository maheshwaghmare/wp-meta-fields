# WP Meta Box Fields
WordPress custom field framework for theme and plugns.

### How to add into theme/plugin?

1. Create `inc` directory in your plugin/theme.
2. Unzip latest release into `inc` directory.
3. Include it into your plugin/theme by adding below code.

```
require_once 'inc/wp-meta-fields/wp-meta-fields.php';
```

### How to add meta box?

Use function `mf_add_meta_box()` to register meta box and its meta fields. It contain parameters which are used for WordPress function [add_meta_box()](https://developer.wordpress.org/reference/functions/add_meta_box/)

E.g.

```
mf_add_meta_box( array(
	'id'       => 'example-all-fields',
	'title'    => __( 'Example - All Fields' ),
	'screen'   => array( 'post' ),
	'context'  => 'normal',
	'priority' => 'default',
	'fields'   => array(
		// ..
	)
));

```

Where,

- `id`	(string) (Required) Meta box ID (used in the 'id' attribute for the meta box).
- `title`	(string) (Required) Title of the meta box.
- `screen`	(string|array|WP_Screen) (Optional) The screen or screens on which to show the box (such as a post type, 'link', or 'comment'). Accepts a single screen ID, WP_Screen object, or array of screen IDs. Default is the current screen. If you have used add_menu_page() or add_submenu_page() to create a new screen (and hence screen_id), make sure your menu slug conforms to the limits of sanitize_key() otherwise the 'screen' menu may not correctly render on your page.
Default value: null
- `context`	(string) (Optional) The context within the screen where the boxes should display. Available contexts vary from screen to screen. Post edit screen contexts include 'normal', 'side', and 'advanced'. Comments screen contexts include 'normal' and 'side'. Menus meta boxes (accordion sections) all use the 'side' context. Global
Default value: 'advanced'
- `priority`	(string) (Optional) The priority within the context where the boxes should show ('high', 'low').
Default value: 'default'

### How to add fields?

Lets add one `text` field in our above registered meta box.

```
mf_add_meta_box( array(
	'id'       => 'example-all-fields',
	'title'    => __( 'Example - All Fields' ),
	'screen'   => array( 'post' ),
	'context'  => 'normal',
	'priority' => 'default',
	'fields' => array(
		'prefix-1-text' => array(
			'type'        => 'text',
			'title'       => 'Text Field',
			'description' => 'Text Field field description goes here.',
			'hint'        => 'Text Field field description goes here.',
			'default'     => '',
		),
	),
);
```

Here,
- `prefix-1-text` 	Unique meta key.
- `type`	Field type.
- `title` 	Field title.
- `description`	Field description.
- `hint`	Field hint.
- `default`	Field default value.

### How to retrieve meta field value.

1. Use shortcode `[mf meta_key="META_KEY" post_id="POST_ID"]` to retrieve the meta value.

E.g.

```
[mf meta_key='prefix-1-text']
```

By default it get the current post ID by using function `get_the_ID()`.

OR

Specific post meta value by passing post ID. E.g.

```
[mf meta_key='prefix-1-text' post_id='46']
```


2. Use function `mf_get_meta()` to retrieve the meta value.

E.g. 

```
<?php echo mf_get_meta( 'prefix-1-text' ); ?>
```

By default it get the current post ID by using function `get_the_ID()`.

OR

Specific post meta value by passing post ID. E.g.

```
<?php echo mf_get_meta( 'prefix-1-text', 46 ); ?>
```

### Field Types

Now, Framework support below build in HTML5 field support.

- text
- textarea
- password
- color
- date
- datetime-local
- email
- month
- number
- time
- week
- url
- checkbox
- radio
- select

### Examples

#### All Meta Field:
```
/**
 * Meta Fields (Screen - Normal)
 */
mf_add_meta_box( array(
	'id'       => 'example-all-fields',
	'title'    => __( 'Example - All Fields' ),
	'screen'   => array( 'post' ),
	'context'  => 'normal',
	'priority' => 'default',
	'fields' => array(
		'prefix-1-text' => array(
			'type'        => 'text',
			'title'       => 'Text Field',
			'description' => 'Text Field field description goes here.',
			'hint' => 'Text Field field description goes here.',
			'default'     => '',
		),
		'prefix-1-textarea' => array(
			'type'        => 'textarea',
			'title'       => 'Textarea',
			'description' => 'Textarea field description goes here.',
			'hint' => 'Textarea field description goes here.',
			'default'     => '',
		),
		'prefix-1-password' => array(
			'type'        => 'password',
			'title'       => 'Password',
			'description' => 'Password field description goes here.',
			'hint' => 'Password field description goes here.',
			'default'     => '',
		),
		'prefix-1-color' => array(
			'type'        => 'color',
			'title'       => 'Color',
			'description' => 'Color field description goes here.',
			'hint' => 'Color field description goes here.',
			'default'     => '#f3f3f3',
		),
		'prefix-1-date' => array(
			'type'        => 'date',
			'title'       => 'Date',
			'description' => 'Date field description goes here.',
			'hint' => 'Date field description goes here.',
			'default'     => '',
		),
		'prefix-1-datetime-local' => array(
			'type'        => 'datetime-local',
			'title'       => 'Date Time Local',
			'description' => 'Date Time Local field description goes here.',
			'hint' => 'Date Time Local field description goes here.',
			'default'     => '',
		),
		'prefix-1-email' => array(
			'type'        => 'email',
			'title'       => 'Email',
			'description' => 'Email field description goes here.',
			'hint' => 'Email field description goes here.',
			'default'     => '',
		),
		'prefix-1-month' => array(
			'type'        => 'month',
			'title'       => 'Month',
			'description' => 'Month field description goes here.',
			'hint' => 'Month field description goes here.',
			'default'     => '',
		),
		'prefix-1-number' => array(
			'type'        => 'number',
			'title'       => 'Number',
			'description' => 'Number field description goes here.',
			'hint' => 'Number field description goes here.',
			'default'     => '',
		),
		'prefix-1-time' => array(
			'type'        => 'time',
			'title'       => 'Time',
			'description' => 'Time field description goes here.',
			'hint' => 'Time field description goes here.',
			'default'     => '',
		),
		'prefix-1-week' => array(
			'type'        => 'week',
			'title'       => 'Week',
			'description' => 'Week field description goes here.',
			'hint' => 'Week field description goes here.',
			'default'     => '',
		),
		'prefix-1-url' => array(
			'type'        => 'url',
			'title'       => 'Url',
			'description' => 'Url field description goes here.',
			'hint' => 'Url field description goes here.',
			'default'     => '',
		),
		'prefix-1-checkbox' => array(
			'type'        => 'checkbox',
			'title'       => 'Checkbox',
			'description' => 'Checkbox field description goes here.',
			'hint'        => 'Checkbox field description goes here.',
			'default'     => true,
		),
		'prefix-1-radio' => array(
			'type'        => 'radio',
			'title'       => 'Radio',
			'description' => 'Radio field description goes here.',
			'hint' => 'Radio field description goes here.',
			'default'     => 'one',
			'choices' => array(
				'one'   => 'One',
				'two'   => 'Two',
				'three' => 'Three',
			),
		),
		'prefix-1-select' => array(
			'type'        => 'select',
			'title'       => 'Select',
			'description' => 'Select field description goes here.',
			'hint' => 'Select field description goes here.',
			'default'     => 'one',
			'choices' => array(
				'one'   => 'One',
				'two'   => 'Two',
				'three' => 'Three',
			),
		),
	)
) );
```
It generate the meta box and meta fields like below screenshot.

![All Meta Box](https://i.imgur.com/s2JorqQ.png)
