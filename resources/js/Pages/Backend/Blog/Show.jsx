import BackendLayout from '@/Layouts/BackendLayout'
import React from 'react'

const Show = () => {
  return (
    <div>
      this is blog show page
    </div>
  )
}

Show.layout = page => <BackendLayout children={page} title="Category Details" />
export default Show
