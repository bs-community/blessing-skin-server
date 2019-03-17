module.exports = {
  create(context) {
    return {
      CallExpression(node) {
        const args = node.arguments
        if (
          node.callee.name === 'test' &&
          args[0].type === 'Literal' &&
          args[1].type === 'ArrowFunctionExpression'
        ) {
          const next = context.getTokenAfter(node)
          if (
            next &&
            next.value === 'test' &&
            node.loc.end.line + 1 === next.loc.start.line
          ) {
            context.report({
              node: context.getLastToken(node),
              message: 'Linebreak should be inserted between test blocks.',
              fix: fixer => fixer.insertTextAfter(node, '\n'),
            })
          }
        }
      },
    }
  },
}
