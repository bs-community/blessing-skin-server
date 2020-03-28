import React from 'react'

export interface Props {
  text?: string
  dangerousHTML?: string
}

const ModalContent: React.FC<Props> = (props) => {
  if (props.children) {
    return <>{props.children}</>
  } else if (props.text) {
    return (
      <>
        {props.text.split(/\r?\n/).map((line, i) => (
          <p key={i}>{line}</p>
        ))}
      </>
    )
  } else if (props.dangerousHTML) {
    return <div dangerouslySetInnerHTML={{ __html: props.dangerousHTML }} />
  }

  return <></>
}

export default ModalContent
