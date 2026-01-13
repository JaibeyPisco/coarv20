export const CurrentDate = () => {
    const today = new Date();
    return today.toISOString().split('T')[0];
};

export const CurrentDateTime = () => {
    const now = new Date();
    return now.toISOString().slice(0, 16); // YYYY-MM-DDTHH:mm
};

export const formatTimeAndDate = (datetime: string | Date) => {
    const date = new Date(datetime);

    const formattedDate = date.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });

    const formattedTime = date.toLocaleTimeString('es-ES', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });

    return `${formattedDate} ${formattedTime}`;
};
