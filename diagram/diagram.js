// http://www.xanthir.com/etc/railroad-diagrams/generator.html
// 'Diagram', 'Sequence', 'Choice', 'Optional', 'OneOrMore', 'ZeroOrMore', 'Terminal', 'NonTerminal', 'Comment', 'Skip'
// map/object

Diagram(
    Terminal('{'),
    ZeroOrMore(
        Sequence(NonTerminal('string'), Terminal(':'), NonTerminal('value')),
        Terminal(',')
    ),
    Terminal("}")
)

// array
Diagram(
    Terminal('['),
    ZeroOrMore(
        NonTerminal('value'),
        Terminal(',')
    ),
    Terminal("]")
)


//value
Diagram(
    Choice(3,
        Terminal('null'),
        Terminal('true'),
        Terminal('false'),
        NonTerminal('string'),
        NonTerminal('number'),
        NonTerminal('array'),
        NonTerminal('object')
    )
)

//  string
Diagram(
    Choice(0,
        ZeroOrMore(
            Choice(0, 'any characters',
                Sequence(
                    Terminal('\\'),
                    Choice(3,
                        Sequence('"', Comment('quotation mark ')),
                        Sequence('\\', Comment('reverse solidus')),
                        Sequence('/', Comment('solidus        ')),
                        Sequence('b', Comment('backspace      ')),
                        Sequence('f', Comment('formfeed       ')),
                        Sequence('n', Comment('newline        ')),
                        Sequence('r', Comment('carriage return')),
                        Sequence('t', Comment('horizontal tab '))
                    )
                )
            )
        ),
        Sequence(
            Choice(0,
                Comment('contain ,[]{}'),
                Comment('null/true/false'),
                Sequence(Comment('is map key'), Choice(0,
                    Sequence(Comment('yes'),
                        Choice(0,
                            Sequence(Comment('contain :')),
                            Sequence(Comment('string length is 0')),
                            Sequence(Comment('mode js'),
                                Choice(2,
                                    Comment('not match /^[a-zA-Z_][a-zA-Z0-9_]*$/'),
                                    Comment('numeric str start with 0(not double)'),
                                    Comment('break'),
                                    Comment('try'),
                                    Comment('new'),
                                    Comment('...(keywords)')
                                )
                            )
                        )
                    ),
                    Sequence(Comment('no'),
                        Choice(0,
                            Sequence(Comment('mode js'),
                                Choice(0,
                                    Comment('not numeric str'),
                                    Comment('numeric str start with 0(not double)')
                                )
                            ),
                            Sequence(Comment('mode strict'),
                                Choice(0,
                                    Comment('numeric str start with 0(not double)')
                                )
                            )
                        )
                    )
                ))
            ),
            Terminal('"'),
            ZeroOrMore(Choice(0, 'any characters',
                Sequence(
                    Terminal('\\'),
                    Choice(3,
                        Sequence('"', Comment('quotation mark ')),
                        Sequence('\\', Comment('reverse solidus')),
                        Sequence('/', Comment('solidus        ')),
                        Sequence('b', Comment('backspace      ')),
                        Sequence('f', Comment('formfeed       ')),
                        Sequence('n', Comment('newline        ')),
                        Sequence('r', Comment('carriage return')),
                        Sequence('t', Comment('horizontal tab '))
                    )
                )
            )),
            Terminal('"')
        )
    )
)


// number
Diagram(
    Optional('-', 'skip'),
    Choice(0,
        Sequence(NonTerminal('digit'), Terminal('.'), NonTerminal('digit')),
        NonTerminal('digit')
    )
)