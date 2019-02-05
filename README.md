# WP Meta Box Fields
WordPress custom field framework for theme and plugns.

### How to use?

1. Create `inc` directory in your plugin/theme.
2. Unzip latest release into it.
3. Include it into your plugin/theme by adding below code.

```
require_once 'inc/wp-meta-fields/wp-meta-fields.php';
```

4. Use function `wp_add_meta_box()` to register meta box and meta fields.

### Examples

#### All Meta Field:
```
/**
 * Meta Fields (Screen - Normal)
 */
wp_add_meta_box( array(
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

![All Meta Box](http://tinyurl.com/y8kmg3ws)