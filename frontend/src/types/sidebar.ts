export interface SidebarItem {
    label: string;
    href?: string;
    icon?: string;
    active?: boolean;
    children?: SidebarItem[];
    method?: string;
    as?: string;
    badge?: {
        text: string;
        variant?: string;
    };
}

