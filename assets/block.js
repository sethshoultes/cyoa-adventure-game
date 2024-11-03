(function(blocks, element, components, editor) {
    var el = element.createElement;

    blocks.registerBlockType('cyoa-adventure-game/adventure-game', {
        title: 'Adventure Game',
        icon: 'games',
        category: 'widgets',
        attributes: {
            content: {
                type: 'array',
                source: 'children',
                selector: 'p',
            }
        },
        
        edit: function(props) {
            return el(
                'div',
                { className: props.className },
                el(components.Placeholder, {
                    icon: 'games',
                    label: 'Adventure Game',
                },
                el('p', {}, 'Your exciting adventure awaits! This block will display the Adventure Game when viewed on the front end.'),
                el(components.Button, {
                    isPrimary: true,
                    onClick: function() {
                        // This is where you could add functionality to configure the game
                        alert('Game configuration would go here!');
                    }
                }, 'Configure Game')
                )
            );
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