(function(blocks, element, components, editor) {
    var el = element.createElement;

    blocks.registerBlockType('cyoa-adventure-game/adventure-game', {
        title: 'Adventure Game',
        icon: 'games',
        category: 'widgets',
        attributes: {
            role_id: {
                type: 'string',
                default: ''
            },
            game_state_id: {
                type: 'string',
                default: ''
            }
        },
        
        edit: function(props) {
            var InspectorControls = editor.InspectorControls;
            var TextControl = components.TextControl;

            return [
                el(
                    InspectorControls,
                    { key: 'inspector' },
                    el(TextControl, {
                        label: 'Role ID',
                        value: props.attributes.role_id,
                        onChange: function(newVal) {
                            props.setAttributes({ role_id: newVal });
                        }
                    }),
                    el(TextControl, {
                        label: 'Game State ID',
                        value: props.attributes.game_state_id,
                        onChange: function(newVal) {
                            props.setAttributes({ game_state_id: newVal });
                        }
                    })
                ),
                el(
                    'div',
                    { className: props.className },
                    el(components.Placeholder, {
                        icon: 'games',
                        label: 'Adventure Game',
                    },
                    el('p', {}, 'Your exciting adventure awaits! This block will display the Adventure Game when viewed on the front end.'),
                    el(components.Button, {
                        isPrimary: true,
                    }, 'Configure Game')
                    )
                )
            ];
        },
        
        save: function() {
            return null; // Dynamic block, render on PHP side
        },
    });
}(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.editor
));