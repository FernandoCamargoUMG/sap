/**
 * Configuración regional para Guatemala
 * Sistema de Administración Presupuestaria (SAP)
 * Universidad Mariano Gálvez de Guatemala
 */

export const guatemalaConfig = {
  // Configuración de moneda
  currency: {
    code: 'GTQ',
    symbol: 'Q',
    locale: 'es-GT',
    decimals: 2
  },

  // Configuración de fechas
  date: {
    locale: 'es-GT',
    format: {
      short: { day: '2-digit', month: '2-digit', year: 'numeric' },
      long: { 
        day: '2-digit', 
        month: 'long', 
        year: 'numeric',
        weekday: 'long'
      },
      datetime: {
        day: '2-digit',
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
      }
    }
  },

  // Validaciones específicas de Guatemala
  validation: {
    nit: {
      pattern: /^[0-9]{1,8}-?[0-9kK]$/,
      example: '12345678-9'
    },
    dpi: {
      pattern: /^[0-9]{13}$/,
      example: '1234567890123'
    },
    telefono: {
      pattern: /^[0-9]{8}$/,
      example: '12345678'
    },
    celular: {
      pattern: /^[345][0-9]{7}$/,
      example: '31234567'
    }
  },

  // Información institucional
  institution: {
    name: 'Universidad Mariano Gálvez de Guatemala',
    shortName: 'UMG',
    year: '2025',
    country: 'Guatemala',
    timezone: 'America/Guatemala'
  }
}

/**
 * Formatear moneda guatemalteca
 * @param {number} amount - Cantidad a formatear
 * @returns {string} - Cantidad formateada
 */
export const formatMoney = (amount) => {
  if (!amount && amount !== 0) return 'Q0.00'
  return parseFloat(amount).toLocaleString(guatemalaConfig.currency.locale, {
    minimumFractionDigits: guatemalaConfig.currency.decimals,
    maximumFractionDigits: guatemalaConfig.currency.decimals
  })
}

/**
 * Formatear fecha guatemalteca
 * @param {string|Date} dateString - Fecha a formatear
 * @param {string} format - Tipo de formato (short, long, datetime)
 * @returns {string} - Fecha formateada
 */
export const formatDate = (dateString, format = 'short') => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString(guatemalaConfig.date.locale, guatemalaConfig.date.format[format])
}

/**
 * Formatear fecha y hora guatemalteca
 * @param {string|Date} dateString - Fecha/hora a formatear
 * @returns {string} - Fecha y hora formateada
 */
export const formatDateTime = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleString(guatemalaConfig.date.locale, guatemalaConfig.date.format.datetime)
}

/**
 * Validar NIT guatemalteco
 * @param {string} nit - NIT a validar
 * @returns {boolean} - Es válido
 */
export const validateNIT = (nit) => {
  return guatemalaConfig.validation.nit.pattern.test(nit)
}

/**
 * Validar teléfono guatemalteco
 * @param {string} phone - Teléfono a validar
 * @returns {boolean} - Es válido
 */
export const validatePhone = (phone) => {
  return guatemalaConfig.validation.telefono.pattern.test(phone) || 
         guatemalaConfig.validation.celular.pattern.test(phone)
}

/**
 * Formatear NIT guatemalteco
 * @param {string} nit - NIT a formatear
 * @returns {string} - NIT formateado
 */
export const formatNIT = (nit) => {
  if (!nit) return ''
  const cleaned = nit.replace(/[^0-9kK]/g, '')
  if (cleaned.length >= 2) {
    const digits = cleaned.slice(0, -1)
    const check = cleaned.slice(-1)
    return `${digits}-${check}`
  }
  return cleaned
}

export default guatemalaConfig