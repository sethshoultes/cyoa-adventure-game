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
    blocks.registerBlockType('cyoa-adventure-game/adventure-game-history', {
        title: 'Adventure Game History',
        icon: 'list-view',
        category: 'widgets',
        edit: function() {
            return el(
                'div',
                { 
                    className: 'wp-block-cyoa-adventure-game-adventure-game-history',
                    style: {
                        backgroundColor: '#f0f0f0',
                        border: '2px solid #ddd',
                        borderRadius: '8px',
                        padding: '20px',
                        margin: '20px 0',
                        boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1)'
                    }
                },
                el('div', { 
                    className: 'cyoa-history-icon',
                    style: {
                        display: 'flex',
                        justifyContent: 'center',
                        marginBottom: '10px'
                    }
                },
                    el('span', { 
                        className: 'dashicons dashicons-list-view',
                        style: {
                            fontSize: '40px',
                            width: '40px',
                            height: '40px',
                            color: '#0073aa'
                        }
                    })
                ),
                el('h3', { style: { textAlign: 'center', margin: '0 0 10px 0', color: '#23282d' } }, 'Adventure Game History'),
                el('p', { style: { fontSize: '16px', lineHeight: '1.6', color: '#555', margin: '0', textAlign: 'center' } }, 'This block will display the Adventure Game History on the front end.')
            );
        },
        save: function() {
            return null; // Dynamic block, render on PHP side
        }
    });
}(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.editor
));