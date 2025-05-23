import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
import LoginPage from "../views/LoginPage.vue";
import SignupPage from "../views/SignupPage.vue";
import AdminPage from "../views/AdminPage.vue";
import Vendorlogin from "../views/Vendorlogin.vue";
import AddCategory from "../views/AddCategory.vue";
import AddSubCategory from "../views/AddSubCategory.vue";
import VendorPage from "../views/VendorPage.vue";
import ProductSearch from "../views/ProductSearch.vue";
import CategoryVendor from "../views/CategoryVendor.vue";
import SubcategoryVendor from "../views/SubcategoryVendor.vue";
import AddProduct from "../views/AddProduct.vue";

const routes = [
  {
    path: "/",
    name: "home",
    component: HomeView,
  },
  {
    path: "/SubcategoryVendor",
    name: "SubcategoryVendor",
    component: SubcategoryVendor,
  },
  {
    path: "/AddProduct",
    name: "AddProduct",
    component: AddProduct,
  },
  {
    path: "/CategoryVendor",
    name: "CategoryVendor",
    component: CategoryVendor,
  },
  {
    path: "/Vendorlogin",
    name: "Vendorlogin",
    component: Vendorlogin,
  },
  {
    path: "/vendors",
    name: "VendorPage",
    component: VendorPage,
  },
  {
    path: "/products",
    name: "ProductSearch",
    component: ProductSearch,
  },
  {
    path: "/AddSubCategory",
    name: "AddSubCategory",
    component: AddSubCategory,
  },
  {
    path: "/AddCategory",
    name: "AddCategory",
    component: AddCategory,
  },
  {
    path: "/AdminPage",
    name: "AdminPage",
    component: AdminPage,
  },
  {
    path: "/signup",
    name: "signup",
    component: SignupPage,
  },
  {
    path: "/LoginPage",
    name: "LoginPage",
    component: LoginPage,
  },
  {
    path: "/about",
    name: "about",
    component: () =>
      import(/* webpackChunkName: "about" */ "../views/AboutView.vue"),
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

export default router;
