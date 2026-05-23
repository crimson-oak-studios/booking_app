export function useFormatters() {
  const currency = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  })

  const date = new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })

  const time = new Intl.DateTimeFormat('en-US', {
    hour: 'numeric',
    minute: '2-digit'
  })

  const weekday = new Intl.DateTimeFormat('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric'
  })

  function formatMoney(cents: number | null | undefined) {
    return currency.format((cents || 0) / 100)
  }

  function formatDate(value: string | null | undefined) {
    return value ? date.format(new Date(value)) : 'Not set'
  }

  function formatTime(value: string | null | undefined) {
    return value ? time.format(new Date(value)) : 'Not set'
  }

  function formatDateTime(value: string | null | undefined) {
    if (!value) {
      return 'Not set'
    }

    return `${formatDate(value)} at ${formatTime(value)}`
  }

  function formatWeekday(value: string | null | undefined) {
    return value ? weekday.format(new Date(value)) : 'Not set'
  }

  function toDateTimeLocal(value: string | null | undefined) {
    if (!value) {
      return ''
    }

    const parsed = new Date(value)
    if (Number.isNaN(parsed.getTime())) {
      return ''
    }

    const pad = (part: number) => String(part).padStart(2, '0')
    return `${parsed.getFullYear()}-${pad(parsed.getMonth() + 1)}-${pad(parsed.getDate())}T${pad(parsed.getHours())}:${pad(parsed.getMinutes())}`
  }

  return {
    formatMoney,
    formatDate,
    formatTime,
    formatDateTime,
    formatWeekday,
    toDateTimeLocal
  }
}
